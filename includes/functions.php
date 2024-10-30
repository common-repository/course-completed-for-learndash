<?php

function trcc_is_administrator() {
    return current_user_can( 'manage_options' );
}

function trcc_get_public_post_types() {
	$args = array(
		'public'   => true,
	 );
	return get_post_types($args); 
}

function trcc_is_excepted_arrays( $array_options, $array_wp ) {
	return $array_options && is_array($array_options) && $array_wp && is_array($array_wp) && count(array_intersect($array_wp, $array_options )); 
}

function trcc_get_role_names($except_admin_roles = false) {
	global $wp_roles;
	if (!isset( $wp_roles)) {
		$wp_roles = new WP_Roles();
	}
	$roles = [];
	foreach ($wp_roles->roles as $k => $r) {
		$caps = $r['capabilities'];
		//Do not include administrator
		if( isset($caps['manage_options']) ) {
			continue;
		}
		//Do not include users with "edit_posts" cap (if param == true)
		if($except_admin_roles) {
			if( isset($caps['edit_posts']) && $caps['edit_posts'] ) {
				continue;
			}
		}
		$roles[] = $k;
	}
	return $roles;
}

//Defines custom text for course_completed/lesson_non_available alert box
function truc_lesson_not_available_text($message, $post) {
    
	$course_id = learndash_get_course_id( $post );
    if(!$course_id) {
		return $message;
	}
	
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		return $message;
	}
    
	$is_completed = learndash_course_completed( $user_id, $course_id );
    if(!$is_completed) {
		return $message;
	}

	$tags_allowed = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	);
    
	$output = (!empty(TRCC_COURSE_COMPLETED_TEXT)) ? TRCC_COURSE_COMPLETED_TEXT : 'You\'ve already completed this course';

	return wp_kses( $output,$tags_allowed );
	
}
//callback for add_filter('learndash_lesson_available_from_text', 'truc_lesson_not_available_text', 1, 2 );


function truc_hide_course_completed_lessons_topics_quizzes( $content, $post ) {

	if ( empty( $post->post_type ) ) {
		return $content;
	}

    $post_types = [
        'sfwd-lessons',
        'sfwd-topic',
        'sfwd-quiz'
    ];

	if ( !in_array($post->post_type, $post_types) ) {
		return $content;
	} 

    $inner_id = $post->ID;
    $course_id = learndash_get_course_id( $post );

	if ( empty( $inner_id ) || empty( $course_id ) ) {
		return $content;
	}

	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$user_id = $user->ID;
		$user_roles = (array) $user->roles;
	} else {
		return $content;
	}

    $has_access   = sfwd_lms_has_access( $course_id, $user_id );
    if ( empty( $has_access ) ) {
		return $content;
	}
	$is_completed = learndash_course_completed( $user_id, $course_id );
    if ( empty( $is_completed ) ) {
		return $content;
	}

	//Check user

	//Administrator can always see content
	if (trcc_is_administrator()) {
		return $content;
	}

	//Users excepted (by role) can always see content
	if (trcc_is_excepted_arrays( TRCC_ROLES_EXCEPTED, $user_roles )) {
		return $content; 
	}

	
	//Check course access mode (is_excepted?)
    $course_meta = get_post_meta($course_id);
    if(empty($course_meta) || empty($course_meta['_ld_price_type'])) {
        return $content;
    }
    
    $price_type = [ $course_meta['_ld_price_type'][0] ]; //array, to allow the use of trcc_is_excepted_arrays()...
	if (trcc_is_excepted_arrays( TRCC_COURSES_ACCESS_MODE_EXCEPTED, $price_type )) {
		return $content; 
	}


    // add_filter('ld-alert-type', function($type) {
    //     return 'success';
    // }); //text is colored white and can't be seen; let's check that in future iterations...

    add_filter('ld-alert-icon', function($type) {
        return 'ld-alert-icon ld-icon ld-icon-complete';
    });
	
    $content = SFWD_LMS::get_template(
        'learndash_course_lesson_not_available',
        array(
            'user_id'                 => get_current_user_id(),
            'course_id'               => learndash_get_course_id( $course_id ),
            'lesson_id'               => $inner_id,
            'lesson_access_from_int'  => '',
            'lesson_access_from_date' => learndash_adjust_date_time_display( '' ),
            'context'                 => 'lesson',
            'icon' => 'success'
        ),
        false
    );
    return $content;
}
//callback for add_filter( 'learndash_content', 'truc_hide_lesson', 1, 2 );
