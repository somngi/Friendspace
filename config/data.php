<?php

return [

    //All Message
    'message' => [
        'register_success' => 'Register Successful',
        'register_mail_error' => 'Activation Mail not Sent',
        'login_error' => 'Username/email or Password not Match',
        'activate' => 'Activate Successfully',
        'email_not_exist' => 'Email not Exists',
        'reset_mail_success' => 'Reset Password Link Send Successfully',
        'reset_mail_error' => 'Reset Password Link Not Sent',
        'change_pass_success' => 'Password Change Successfully',
        'current_pass_error' => 'Current Password Not Match',
        'logout' => 'Logout_successfully',
        'user_not_find' => 'User Not Found',
        'update_success' => 'Update Successfully',
        'follow_exists' => 'You Already Follow this User',
        'follow_not_exists' => 'You have not Follow this User',
        'follow_success' => 'Follow Success',
        'unfollow_success' => 'UnFollow Success',
        'follow_error' => 'Follow Error',
        'album_exists' => 'Album Already Exists',
        'album_error' => 'Album Not Created',
        'album_success' => 'Album Successfully Created',
    ],

    //Token
    'token' => [
        'invalid' => 'Token is Invalid',
        'expire' => 'Token is Expired',
        'respond' => 'Token is Not Respond',
        'required' => 'Token is Required',
        'generate' => 'Token Not Generate',
        'activate_required' => 'Activation Token is Required',
        'activate_expire' => 'Activation Token is Expired',
    ],

    'url' => [
        'activation' => 'http://localhost:8000/api/activate_account/',
        'reset_password' => 'http://localhost:8000/api/reset_password/',
    ],

    'image' => [
        'profile_pic_icon_width' => 50,
        'profile_pic_icon_height' => 50,
        'profile_pic_thumb_width' => 250,
        'profile_pic_thumb_height' => 250,
        'album_photo_thumb_width' => 250,
        'album_photo_thumb_height' => 250,
        'post_image_thumb_width' => 250,
        'post_image_thumb_height' => 250,
    ],

    'path' => [
        'album' => '{user_id}/{album_name}_{user_id}_str_random(16)/(thumb|main)',
        'album_profile_picture' => '{user_id}/profile_picture/(icon|thumb|main)' ,
        'album_posts' => '{user_id}/posts/(thumb|main',
        'profile_picture' => 'str_random(16)_{user_id}_time().ext',
        'photo' => 'str_random(16)_{album_id}_time().ext',
        'posts_image' => 'str_random(16)_{user_id|album_id|post_id}_time().ext',
    ]





];