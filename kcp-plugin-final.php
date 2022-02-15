<?php
/**
 * Plugin Name:       Krista Colleagues
 * Description:       Custom code for the Krista Colleagues Global Service Program 
 *                    website. Adds a grant application form and editable list of 
 *                    Service Opportunity custom post types. Created for the KCGSP 
 *                    as part of a project for Whitworth University's CS301 course.
 * Version:           1.0
 * Author:            Sarah Immel
 */

register_activation_hook(__FILE__, function(){
    //Make custom post types & refresh permalinks (flush_rewrite_rules())
    
    //Set up default options
    //Add menu item?
});

add_action('init', 'register_application_posts');
function register_application_posts(){
    register_post_type('kcp_application', 
        array('labels' => 
            array('name' => 'Applications', 
            'singular_name' => 'Application'),
        'public' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'has_archive' => false,
        'rewrite' => array( 'slug' => 'applications' ),
        'supports' => array('title','custom-fields','revisions','page-attributes')) );   
        register_taxonomy('University', 'kcp_application', 
                    array('labels'=>array('name'=>'University','singular_name'=>'University'),
                    'public'=>true,         'show_ui'=>false,
                    'show_in_menu'=>true,   'show_admin_column'=>true,));
        register_post_type('kcp_service_opp', 
        array('labels' => 
            array('name' => 'Service Opportunities', 
            'singular_name' => 'Service Opportunity'),
        'public' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => false,
        'show_in_nav_menus' => true,
        'rewrite' => array( 'slug' => 'service-opp' ),
        'supports' => array('title','editor','custom-fields','revisions','page-attributes')) );
        flush_rewrite_rules();
}

/* 404 redirect if a non-logged-in user tries to view a completed application */
/* Adapted from https://wordpress.stackexchange.com/a/368012 */
add_action('template_redirect', 'protect_kcp_applications');
function protect_kcp_applications() {
    global $post;
    if( !is_object($post) ) 
     return;
    else if ($post->post_type == 'kcp_application') {
        if (!is_user_logged_in()) {
            global $wp_query;
            $wp_query->posts = [];
            $wp_query->post = null;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }
}

add_shortcode('appform','appform');
function appform(){
        //Check if form has been posted
        if ( empty( $_POST)){
            //If not, display form
            $formhtml = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>
            <form action='' method='POST' id='kcp_app_form'>"
                .wp_nonce_field( 'kcp_grant_app', 'verify' ).
                "<h2>Global Service Grant Application</h2>
                <label for='name'>Full Name</label>
                    <input type='text' id='name' name='kcp_name' required>
                <label for='email'>Email</label>
                    <input type='email' id='email' name='kcp_email' required>
                <label for='phone'>Phone Number</label>
                    <input type='tel' id='phone' name='kcp_phone' required>
                <label for='address'>Family Home Address</label>
                    <textarea id='address' name='kcp_address' rows='4' cols='40' required></textarea>
                <h3>University</h3>
                    <input type='radio' id='Gonzaga' name='kcp_univ' value='Gonzaga' required>
                    <label for='Gonzaga'>Gonzaga</label>
                    <input type='radio' id='Whitworth' name='kcp_univ' value='Whitworth' required>
                    <label for='Whitworth'>Whitworth</label>
                <div>
                    <label for='agencies'><h3>What agency/agencies have you applied to/been accepted at?</h3></label>
                    <textarea id='agencies' name='kcp_agencies' rows='3' cols='75' required></textarea>
                </div>
                <div>
                    <label for='length'><h3>If you have been accepted, how long is your commitment to service?</h3></label>
                    <textarea id='length' name='kcp_length' rows='3' cols='75'></textarea>
                </div>
                <div>
                    <label for='where'><h3>If you have been accepted, at which potential location(s) will you serve?</h3></label>
                    <textarea id='where' name='kcp_where' rows='3' cols='75'></textarea>
                </div>
                <div>
                    <label for='activities'><h3>In what extracurricular activities and hobbies are you involved?</h3></label>
                    <textarea id='activities' name='kcp_activities' rows='3' cols='75' required></textarea>
                </div>
                <div>
                    <label for='why'><h3>Why do you want to serve? What motivates your choice for post-graduate service? (Please answer in 500 - 800 characters)</h3></label>
                    <textarea id='why' name='kcp_why' rows='6' cols='75' minlength=500 maxlength=800 required></textarea>
                </div>
                <div>
                    <label for='experience'><h3>Describe an earlier volunteer service experience. What did you learn about others and yourself in this opportunity? (Please answer in 500 - 800 characters)</h3></label>
                    <textarea id='experience' name='kcp_experience' rows='6' cols='75' minlength=500 maxlength=800 required></textarea>
                </div>
                <div>
                    <label for='challenge'><h3>Post-graduate service often presents many new challenges. Describe a challenge you've experienced and what steps you took to meet this challenge. (Please answer in 500 - 800 characters)</h3></label>
                    <textarea id='challenge' name='kcp_challenge' rows='6' cols='75' minlength=500 maxlength=800 required></textarea>
                </div>
                <div class='g-recaptcha' data-sitekey='6Ld1wnweAAAAADmFQzSCY2S4dkCVJ1RuvCFdoJFh'></div>
                <input type='submit' value='submit'>
            </form>";
            return $formhtml;
        }else{ //Form has been submitted
            //reCAPTCHA verification adapted from https://codeforgeek.com/google-recaptcha-tutorial/
            if(isset($_POST['g-recaptcha-response'])){
                 $captcha=$_POST['g-recaptcha-response'];
            }
            if(!$captcha){
                      echo 'Complete the reCAPTCHA and try again.';
            }
            $secretKey = "6Ld1wnweAAAAABSn8ObK091P32qcNeXBMJV3myAz";
            $ip = $_SERVER['REMOTE_ADDR'];
            // post request to server
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
            $response = file_get_contents($url);
            $responseKeys = json_decode($response,true);
            // should return JSON with success as true
            //If reCAPTCHA and nonce tests are both successful
            if($responseKeys["success"] && wp_verify_nonce( $_POST['verify'], 'kcp_grant_app' )){
                //Sanitize entries
                $name = sanitize_text_field($_POST["kcp_name"]);
                $email = sanitize_email($_POST["kcp_email"]);
                $phone = sanitize_text_field($_POST["kcp_phone"]);
                $address = sanitize_textarea_field($_POST["kcp_address"]);
                $univ = sanitize_text_field($_POST["kcp_univ"]);
                $agencies = sanitize_textarea_field($_POST["kcp_agencies"]);
                $length = sanitize_textarea_field($_POST["kcp_length"]);
                $where = sanitize_textarea_field($_POST["kcp_where"]);
                $activities = sanitize_textarea_field($_POST["kcp_activities"]);
                $why = sanitize_textarea_field($_POST["kcp_why"]);
                $experience = sanitize_textarea_field($_POST["kcp_experience"]);
                $challenge = sanitize_textarea_field($_POST["kcp_challenge"]);

                //Create post
                $postid = wp_insert_post( array("post_type"=>"kcp_application",
                                        "post_title" => $name,
                                        "post_status" => "publish",
                                        "post_category" => array($univ)));
                if(!$postid){
                    return "Error. Please try again or contact the site administrator.";
                }
                //Add custom fields
                add_post_meta($postid, "University", $univ);
                add_post_meta($postid, "Email", $email);
                add_post_meta($postid, "Phone", $phone);
                add_post_meta($postid, "Address", $address);
                add_post_meta($postid, "Agencies", $agencies);
                add_post_meta($postid, "Length", $length);
                add_post_meta($postid, "Where", $where);
                add_post_meta($postid, "Activities", $activities);
                add_post_meta($postid, "Why", $why);
                add_post_meta($postid, "Experience", $experience);
                add_post_meta($postid, "Challenge", $challenge);
                wp_set_object_terms($postid, $univ, 'University');
                ob_start();
                echo "<h3 style='text-align: center;'>Your application has been submitted. Thank you!</h3>";
                ob_get_clean();
            } 
        }
}

add_shortcode('serviceOppsForm','service_opps_form');
function service_opps_form(){
        //Check if form has been posted
        if ( empty( $_POST)){
            //If not, display form
            $formhtml = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>
            <form action='' method='POST' id='kcp_opps_form'>"
                .wp_nonce_field( 'kcp_add_serv_opp', 'verify' ).
                "<h2>Add a Service Opportunity Listing</h2>
                <label for='opp_name'><h3>Program Name</h3></label>
                    <input type='text' id='opp_name' name='kcp_opp_name' required>
                <label for='opp_url'><h3>Website URL</h3></label>
                    <input type='url' id='opp_url' name='kcp_opp_url' required>
                <label for='opp_desc'><h3>Short Program Description</h3></label>
                    <textarea id='opp_desc' name='kcp_opp_desc' rows='6' cols='75'></textarea>
                <label for='opp_deadline'><h3>Program Application Deadline</h3></label>
                    <input type='text' id='opp_deadline' name='kcp_opp_deadline' required>
                    <div class='g-recaptcha' data-sitekey='6Ld1wnweAAAAADmFQzSCY2S4dkCVJ1RuvCFdoJFh'></div>
                    <input type='submit' value='submit'>
            </form>";
            return $formhtml;
        }else{ //Form has been submitted
            //reCAPTCHA verification adapted from https://codeforgeek.com/google-recaptcha-tutorial/
            if(isset($_POST['g-recaptcha-response'])){
                 $captcha=$_POST['g-recaptcha-response'];
            }
            if(!$captcha){
                      echo 'Complete the reCAPTCHA and try again.';
            }
            $secretKey = "6Ld1wnweAAAAABSn8ObK091P32qcNeXBMJV3myAz";
            $ip = $_SERVER['REMOTE_ADDR'];
            // post request to server
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
            $response = file_get_contents($url);
            $responseKeys = json_decode($response,true);
            // should return JSON with success as true
            //If reCAPTCHA and nonce tests are both successful
            if($responseKeys["success"] && wp_verify_nonce( $_POST['verify'], 'kcp_grant_app' )){
                //Sanitize entries
                $opp_name = sanitize_text_field($_POST["kcp_opp_name"]);
                $opp_url = sanitize_url($_POST["kcp_opp_url"]);
                $opp_desc = sanitize_textarea_field($_POST["kcp_opp_desc"]);
                $opp_deadline = sanitize_text_field($_POST["kcp_opp_deadline"]);

                //Create post AS A DRAFT so admin can approve later
                $postid = wp_insert_post( array("post_type"=>"kcp_service_opp",
                                        "post_title" => $opp_name,
                                        "post_status" => "draft",
                                        "post_content" => $opp_desc)
                );
                if(!$postid){
                    return "Error. Please try again or contact the site administrator.";
                }
                //Add custom fields
                add_post_meta($postid, "URL", $opp_url);
                add_post_meta($postid, "Deadline", $opp_deadline);
                
                ob_start();
                echo "<h3 style='text-align: center;'>Service opportunity submitted. Thank you!</h3>";
                ob_get_clean();
            }
        }
}

add_shortcode('showServiceOpps','kcp_show_service_opps');
function kcp_show_service_opps(){
    $htmltable = "<table><tr><th>Program</th><th>Deadline</th><th>Website</th></tr>";
    $posts = get_posts([
        'post_type' => 'kcp_service_opp',
        'post_status' => 'publish',
        'numberposts' => -1,
        'order'    => 'ASC'
      ]);
    foreach ($posts as $post){
        $htmltable .= "<tr><td><a href='" .$post->guid . "'> ". $post->post_title . "</a> </td>
        <td>" . get_post_meta($post->ID, 'Deadline', true) . "</td>
        <td><a href= '" . get_post_meta($post->ID, 'URL', true) . "' target='_blank'>Visit</a></td></tr>";
    }
    $htmltable .= "</table>";
    return $htmltable;
}
