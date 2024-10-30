<?php

//Define options (keys) and fiels vitals (values)
$options_array = [
    'trcc_courses_access_mode_excepted' => [
        'type' => 'select',
        'kind' => 'multiple',
        'options' => ['open','free','paynow','recurring','closed'],
        'default' => '',
        'description'=> 'Select courses by access mode to NOT be affected by the plugin',
        'obs' => 'you can select as many as you like, by Ctrl+Click (deselect by Ctrl+Click as well).',
        'final' => 'Default: courses with all kinds of access modes will be affected.',
        'order' => 1, 
    ],
    'trcc_roles_excepted' => [
         'type' => 'select',
         'kind' => 'multiple',
         'options' => [],
         'get_options' => 'trcc_get_role_names',
         'default' => '',
         'description'=> 'Select user roles to NOT be affected by the plugin',
         'obs' => 'you can select as many as you like, by Ctrl+Click (deselect by Ctrl+Click as well).',
         'final' => 'Administrators are already excepted from the restriction.',
         'order' => 2,  
    ], 
    'trcc_course_completed_text' => [
        'type' => 'text',
        'kind' => '',
        'default' => 'You\'ve already completed this course',
        'description'=> 'Define the text that the user who has completed the course will see.',
        'obs' => 'this text will replace content on lessons, topics and quizzes pages; placeholder text is the default',
        'final' => 'html tags allowed: <a>  <br>  <em>  <strong>.',
        'order' => 3,
    ],
];

define("TRCC_OPTIONS_ARRAY", $options_array);
foreach(TRCC_OPTIONS_ARRAY as $op => $vals) {
    define(strtoupper($op),get_option($op));
}

function trcc_admin_menu() {
    global $trcc_settings_page;
    $trcc_settings_page = add_submenu_page(
                            'learndash-lms', //The slug name for the parent menu
                            __( 'Course Completed', 'students-count' ), //Page title
                            __( 'Course Completed', 'students-count' ), //Menu title
                            'manage_options', //capability
                            'learndash-course-completed', //menu slug 
                            'trcc_admin_page' //function to output the content
                        );
}
add_action( 'admin_menu', 'trcc_admin_menu' );


function trcc_register_plugin_settings() {
    foreach(TRCC_OPTIONS_ARRAY as $op => $vals) {
        register_setting( 'trcc-settings-group', $op );
    } 
}
//call register settings function
add_action( 'admin_init', 'trcc_register_plugin_settings' );


function trcc_admin_page() {
?>

<div class="trcc-head-panel">
    <h1><?php esc_html_e( 'Course Completed for Learndash', 'learndash-course-completed' ); ?></h1>
    <h3><?php esc_html_e( 'Prevent user from accessing course content after course completion.', 'learndash-course-completed' ); ?></h3>
</div>

<div class="wrap trcc-wrap-grid">

    <form method="post" action="options.php">

        <?php settings_fields( 'trcc-settings-group' ); ?>
        <?php do_settings_sections( 'trcc-settings-group' ); ?>

        <div class="trcc-form-fields">

            <div class="trcc-settings-title">
                <?php esc_html_e( 'Course Completed for Learndash - Settings', 'learndash-course-completed' ); ?>
            </div>

            <?php foreach(TRCC_OPTIONS_ARRAY as $op => $vals)  { ?>

                <div class="trcc-form-fields-label">
                    <?php esc_html_e( $vals['description'], 'learndash-course-completed' ); ?>
                    <?php if(!empty($vals['obs'])) { ?>
                        <span>* <?php esc_html_e( $vals['obs'], 'learndash-course-completed' ); ?></span>
                    <?php } ?>
                </div>
                <div class="trcc-form-fields-group">
                    <?php if($vals['type'] === 'select') { ?>
                        <!-- select -->
                        <div class="trcc-form-div-select">
                            <label>
                                <select name="<?php echo ($vals['kind'] === 'multiple') ? esc_attr( $op ) . '[]' : esc_attr( $op ); ?>"
                                        <?php echo esc_attr($vals['kind']); ?>
                                >
                                    <?php if(empty($vals['options'])) {$vals['options'] = $vals['get_options']();} 
                                    foreach($vals['options'] as $pt) { ?>
                                        <option value="<?php echo esc_attr($pt); ?>"
                                        <?php
                                            if( empty(get_option($op)) && $vals['default'] === $pt ) {
                                                echo esc_attr('selected');
                                            } else if( $vals['kind'] === 'multiple' ) {
                                                if( is_array(get_option($op)) && in_array($pt,get_option($op)) ) {
                                                    echo esc_attr('selected');
                                                }
                                            } else {
                                                selected($pt, get_option($op), true);
                                            }
                                        ?>
                                        >     
                                            <?php echo esc_html($pt); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
                    <?php } else if ($vals['type'] === 'text') { ?>
                        <!-- text -->
                        
                        <input type="text" placeholder="<?php echo esc_attr($vals['default']); ?>" class=""
                            value="<?php echo esc_attr( get_option($op) ); ?>"
                            name="<?php echo esc_attr( $op ); ?>">
                    <?php } ?>
                    <?php if(!empty($vals['final'])) { ?>
                        <span>* <?php esc_html_e($vals['final'], 'learndash-course-completed' ); ?></span>
                    <?php } ?>
                </div>
                <hr>
                <?php } //end foreach TRCC_OPTIONS_ARRAY ?>
               

            <?php submit_button(); ?>

            <div style="float:right; margin-bottom:20px">
              Contact Luis Rock, the author, at 
              <a href="mailto:lurockwp@gmail.com">
                lurockwp@gmail.com
              </a>
            </div>

        </div> <!-- end form fields -->
    </form>
</div> <!-- end trcc-wrap-grid -->
<?php } ?>