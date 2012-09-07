<?php
function scripted_create_a_job_callback()
{
    ?>
       <style>
           .form-table select {
               width: 300px;
           }
       </style>
        <?php
   $apiKey = get_option( '_scripted_api_key' );
   $_scripted_business_id = get_option( '_scripted_business_id' );
   
    if(isset($_POST) && wp_verify_nonce($_POST['_wpnonce'],'createProject')) {        
       $error = validateCreateProject($_POST);       
       if($error == '') {
           $topic           = urlencode(sanitize_text_field($_POST['topic']));
           
           $format_id       = sanitize_text_field($_POST['format_id']);
           $industry_ids    = sanitize_text_field($_POST['industry_ids']);
           $guideline_ids   = sanitize_text_field($_POST['guideline_ids']);
           $delivery        = sanitize_text_field($_POST['delivery']);
           $formFields      = $_POST['form_fields'];
           $fields          ='';
           
           if(is_array($formFields)) {
               foreach($formFields as $key => $value) {
                   $value   = sanitize_text_field($value);
                   $fields  .= '&form_fields['.$key.']='.urlencode($value);
               }
           }
           
           $urlToSendRequest  = 'https://scripted.com/jobs/create?key='.$apiKey.'&sandbox=false&business_id='.$_scripted_business_id.'&topic='.$topic.$fields;
           
           if($format_id!= '')
               $urlToSendRequest .= '&format_id='.$format_id;
           if($industry_ids!= '')
               $urlToSendRequest .= '&industry_ids='.$industry_ids;
           if($guideline_ids!= '')
               $urlToSendRequest .= '&guideline_ids='.$guideline_ids;
           if($delivery!= '')
               $urlToSendRequest .= '&delivery='.$delivery;
           
           $_responseUrl = @file_get_contents($urlToSendRequest);         
           
           if($_responseUrl) {
                $respnoseDecoded = json_decode($_responseUrl);               
                if($respnoseDecoded->id) {
                    $deadlineAt = strtotime($respnoseDecoded->deadline_at);
                    $deadlineAt = '<p>Delivery Time : '.date('M d, Y',$deadlineAt).'</p>';
                    $projectId = '<p>Project id : '.$respnoseDecoded->id.'</p>';

                    echo '<div class="updated" id="message"><p>Congratulation! Your project has been created.</p>'.$projectId.$deadlineAt.'</div>';
                } else {
                    echo '<div class="updated" id="message"><p>Sorry, we found an error and your Scripted job was not created! Please confirm your API key and Business ID are correct and try again.</p></div>';
                }
           } else {
               echo '<div class="updated" id="message"><p>Sorry, we found an error and your Scripted job was not created! Please confirm your API key and Business ID are correct and try again.</p></div>';
           }
           
       } else {
            echo '<div class="updated" id="message">'.$error.'</div>';
       }
    }
  
    
   $out = '<div class="wrap">
            <div class="icon32" style="width:100px;padding-top:5px;" id="icon-scripted"><img src="'.SCRIPTED_LOGO.'"></div><h2>Create a Job</h2>';
            
   $out .='<form action="" method="post" name="scripted_settings">'.wp_nonce_field( 'createProject', '_wpnonce' );
   
   
   if($apiKey !='' and $_scripted_business_id!='') {
       if(isset($_POST['format_id']) and $_POST['format_id'] !='0') {
           $fields = getFormFieldsCallback($_POST['format_id']);
       }
   $out .='<table class="form-table">
            <tbody>
                <tr valign="top">
        <th scope="row"><label for="topic">Topic </label></th>
        <td><input type="text" class="regular-text" value="'.$_POST['topic'].'" id="topic" name="topic"></td>
        </tr>
        <tr valign="top">
        <th scope="row"><label for="api_key">Format </label></th>
        <td>'.getStandardBlogPost($_POST['format_id']).'</td>
        </tr>
        <tr valign="top">
        <td colspan="2" id="formfieldsplace">'.@$fields.'</td>
        </tr>
        <tr valign="top">
        <th scope="row"><label for="api_key">Industries </label></th>
        <td>'.getListIndustryIds($_POST['industry_ids']).'</td>
        </tr>
        <tr valign="top">
        <th scope="row"><label for="api_key">Guidelines </label></th>
        <td>'.getListGuidelineIds($_POST['guideline_ids']).'</td>
        </tr>
        <tr valign="top">
        <th scope="row"><label for="api_key">Delivery </label></th>
        <td>'.delivery($_POST['delivery']).'</td>
        </tr>
            </tbody>
            </table>
            <p class="submit">
            <input type="submit" value="Create this Job" class="button-primary" id="submit" name="submit">
            </p>';
   }
   
   
   $out .='</form>';
   
   $out .='</div>';// end of wrap div
   echo $out;
}
function getStandardBlogPost($selected ='')
{
    $_formateGetUrl = @file_get_contents('https://scripted.com/formats');    
    if($_formateGetUrl) {
        $jsonDecoded = json_decode($_formateGetUrl);

        $out .= '<select name="format_id" onchange="getFormFields(this.value);">';
        $out .='<option value="0">Select</option>';
        foreach($jsonDecoded as $format) { 
            $class = '';
            if($selected !='' and $selected == $format->id) 
                $class = 'selected="selected"';
            $out .='<option value="'.$format->id.'" '.$class.'>'.$format->name.' for $'.$format->price.'</option>';
        }
        $out .='</select>';
        return $out;
    }
}
function getListIndustryIds($selected ='')
{
    $_formateGetUrl = @file_get_contents('https://scripted.com/industries');
    if($_formateGetUrl) {
        $jsonDecoded = json_decode($_formateGetUrl);

        $out .= '<select name="industry_ids">';
        $out .='<option value="">Select one at a time</option>';
        foreach($jsonDecoded as $format) {

            $class = '';
            if($selected !='' and $selected == $format->id) 
                $class = 'selected="selected"';

            $out .='<option value="'.$format->id.'" '.$class.'>'.$format->name.'</option>';
        }
        $out .='</select>';
        return $out;
    }
}
function getListGuidelineIds($selected ='')
{
    $_formateGetUrl = @file_get_contents('https://scripted.com/guidelines');
    if($_formateGetUrl) {
        $jsonDecoded = json_decode($_formateGetUrl);

        $out .= '<select name="guideline_ids">';
        $out .='<option value="">Select one at a time</option>';
        foreach($jsonDecoded as $format) {
            $class = '';
            if($selected !='' and $selected == $format->id) 
                $class = 'selected="selected"';

            $out .='<option value="'.$format->id.'" '.$class.'>'.$format->name.'</option>';
        }
        $out .='</select>';
        return $out;
    }
}
function delivery($selected ='')
{
    $standard = ($selected != '' and $selected=='standard')?'selected="selected"':'';
    $rush = ($selected != '' and $selected=='rush')?'selected="selected"':'';
    
    $out ='<select name="delivery" id="delivery" class="span3">
        <option value="standard" '.$standard.'>Delivered in 5 business days</option>
            <option value="rush" '.$rush.'>Delivered in 3 business days (+$10)</option>
            </select>';
    
    return $out;
}
function validateCreateProject($posted) {
    $error = '';
    if(isset($posted['topic']) and $posted['topic'] =='') {
        $error .= '<p>Topic field can not be empty.</p>';
    }
    if(isset($posted['format_id']) and $posted['format_id'] =='') {
        $error .= '<p>Standard Blog Post field can not be empty.</p>';
    }
  
    return $error;
}
function getFormFields()
{
    ?>
    <script>
        function getFormFields(id) {
                    jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo wp_nonce_url( admin_url('admin-ajax.php'), 'formfields_project' );?>',
                            data: 'form_id='+id+'&action=get_form_fields',
                            success: function(data) {
                                jQuery('#formfieldsplace').html(data);

                        }
                        });
       }
    </script>
        <?php
}
add_action('wp_ajax_get_form_fields', 'getFormFieldsCallback');
function getFormFieldsCallback($postformField = '')
{
    $formField =  $_POST['form_id'];
    if($postformField !='')
        $formField =  $postformField;
    
    $out = '';
    if((isset($_POST) && wp_verify_nonce($_GET['_wpnonce'],'formfields_project') and $formField !='0') or $postformField!='') {
        $_formateGetUrl = @file_get_contents('https://scripted.com/formats');    
        if($_formateGetUrl) {
            $jsonDecoded = json_decode($_formateGetUrl);
            $out .= '<ul>';
            foreach($jsonDecoded as $format) { 
             
                if($format->id == $formField) {
                    $fields = $format->form_fields;
                    foreach($fields as $field) {
                        if(in_array($field[0],textAreaFields())) {
                            $out .='<li><label style="width:220px; float:left;">'.$field[1].'</label><textarea name="form_fields['.$field[0].']" cols="48" rows="5" class="span3">'.@$_POST['form_fields'][$field[0]].'</textarea><p style="margin-left:220px; font-size:10px;">'.$field[2].'</p></li>';
                        } else {
                            $out .='<li><label style="width:220px; float:left;">'.$field[1].'</label><input class="regular-text" type="text" name="form_fields['.$field[0].']" value="'.@$_POST['form_fields'][$field[0]].'" /><p style="margin-left:220px; font-size:10px;">'.$field[2].'</p></li>';
                        }
                    }
                }
            }
            $out .= '<li>';
            
        }
        
    }
    if($postformField !='')
        return $out;
    else
        echo $out;
    die();
}
function textAreaFields() {
    return array('keywords','additional_notes','quotes','your_company');
}