<?php
function scripted_create_finished_jobs_callback()
{
    $apiKey                 = get_option( '_scripted_api_key' );
    $_scripted_business_id  = get_option( '_scripted_business_id' );
    $paged                  = (isset($_GET['paged']) and $_GET['paged'] !='') ? $_GET['paged'] : 1;
    $per_page               = 10;
    
    $validate = validateApiKey($apiKey,$_scripted_business_id);
    
    $out = '<div class="wrap">
            <div class="icon32" style="width:100px;padding-top:5px;" id="icon-scripted"><img src="'.SCRIPTED_LOGO.'"></div><h2>Finished Jobs <a class="add-new-h2" href="admin.php?page=scripted_create_a_job">Create a Job</a></h2>';
    
    if($validate) {
        $_finishedJobs = @file_get_contents('https://scripted.com/finished_jobs?key='.$apiKey.'&business_id='.$_scripted_business_id.'&page='.$paged.'&per_page='.$per_page.'&');            
        $_finishedJobs = json_decode($_finishedJobs);        
        
        $totalProjects  = $_finishedJobs->total;
        $totalPages = ceil($totalProjects/$per_page);
        
        
        // paggination
         $pageOne = '';         
         if($totalPages < 2)
             $pageOne = ' one-page';     
         
         $out .='<div class="tablenav top">
            <div class="tablenav-pages'.$pageOne.'">';
         
                $out .='<span class="displaying-num">'.$totalProjects.' items</span>';
                $prePage = '';
                if($paged < 2) 
                    $prePage = 'disabled';
                $nextPage = '';
                if($totalPages == $paged) 
                    $nextPage = 'disabled';
                
                $preLink = 1;
                if($paged > 1) 
                    $preLink = $paged-1;
                $nextLink = $totalPages;
                if($paged < $totalPages) 
                    $nextLink = $paged+1;
                
                $out .='<span class="pagination-links"><a href="admin.php?page=scripted_create_finished_jobs&paged=1" title="Go to the first page" class="first-page  '.$prePage.'">&laquo;</a>
                        <a href="admin.php?page=scripted_create_finished_jobs&paged='.$preLink.'" title="Go to the previous page" class="prev-page '.$prePage.'">&lsaquo;</a> 
                            <span class="paging-input">'.$paged.' of <span class="total-pages">'.$totalPages.'</span></span>
                            <a href="admin.php?page=scripted_create_finished_jobs&paged='.$nextLink.'" title="Go to the next page" class="next-page '.$nextPage.'">&rsaquo;</a>
                        <a href="admin.php?page=scripted_create_finished_jobs&paged='.$totalPages.'" title="Go to the last page" class="last-page '.$nextPage.'">&raquo;</a>';
         
                   $out .='</span>
             </div>
            <br class="clear">
            </div>';
        // paggination end
        
        
        $out .='<table cellspacing="0" class="wp-list-table widefat fixed pages">
                    <thead>
                        <tr>
                        <th class="manage-column column-author" scope="col"><span>Topic</span></th>
                        <th class="manage-column column-author" scope="col"><span>State</span></th>
                        <th class="manage-column column-author" scope="col"></th>
                        </tr>
                    </thead>
                      <tbody id="the-list">
                    ';
        
        if($_finishedJobs->total) {
            $finishedJobs = $_finishedJobs->jobs;
            $i = 1;
            foreach($finishedJobs as $job) {
                $out .='<tr valign="top" class="scripted type-page status-publish hentry alternate">
                    <input type="hidden" id="project_'.$i.'" value="'.$job->id.'">
                    <td class="author column-author"><strong>'.$job->topic.'</strong></td>
                    <td class="author column-author">'.$job->state.'</td>
                    <td class="author column-author"><span  id="create_'.$job->id.'"><a href="javascript:void(0)" onclick="createProject('.$i.')">Create Draft</a></span></td>
                    </tr>';
                $i++;
            }
            
        } else {
            $out .='<tr valign="top" class="scripted type-page status-publish hentry alternate">
                    <th colspan="4"  style="text-align:center;" class="check-column"><strong>Your Scripted account has no Finished Jobs. <a href="admin.php?page=scripted_create_a_job">Create a Job</a></strong></td>
                    </tr>';
        }
         $out .= '</tbody>
                </table>
                '; // end table
         
         // paggination
         $pageOne = '';         
         if($totalPages < 2)
             $pageOne = ' one-page';     
         
         $out .='<div class="tablenav bottom">
            <div class="tablenav-pages'.$pageOne.'">';
         
                $out .='<span class="displaying-num">'.$totalProjects.' items</span>';
                $prePage = '';
                if($paged < 2) 
                    $prePage = 'disabled';
                $nextPage = '';
                if($totalPages == $paged) 
                    $nextPage = 'disabled';
                
                $preLink = 1;
                if($paged > 1) 
                    $preLink = $paged-1;
                $nextLink = $totalPages;
                if($paged < $totalPages) 
                    $nextLink = $paged+1;
                
                $out .='<span class="pagination-links"><a href="admin.php?page=scripted_create_finished_jobs&paged=1" title="Go to the first page" class="first-page  '.$prePage.'">&laquo;</a>
                        <a href="admin.php?page=scripted_create_finished_jobs&paged='.$preLink.'" title="Go to the previous page" class="prev-page '.$prePage.'">&lsaquo;</a> 
                            <span class="paging-input">'.$paged.' of <span class="total-pages">'.$totalPages.'</span></span>
                            <a href="admin.php?page=scripted_create_finished_jobs&paged='.$nextLink.'" title="Go to the next page" class="next-page '.$nextPage.'">&rsaquo;</a>
                        <a href="admin.php?page=scripted_create_finished_jobs&paged='.$totalPages.'" title="Go to the last page" class="last-page '.$nextPage.'">&raquo;</a>';
         
                   $out .='</span>
             </div>
            <br class="clear">
            </div>';
        // paggination end
    }
    
    $out .='</div>';// end of wrap div
    
    echo $out;
}
function createScriptedProject()
{
    global $current_user;
    $userID = $current_user->ID;
    if(isset($_POST) && wp_verify_nonce($_GET['_wpnonce'],'create_project')) {
        $apiKey                 = get_option( '_scripted_api_key' );
        $_scripted_business_id  = get_option( '_scripted_business_id' );
        $proId                  = sanitize_text_field($_POST['proId']);
        $validate = validateApiKey($apiKey,$_scripted_business_id);
        
        if($validate) {
            $_projectContent = @file_get_contents('https://scripted.com/finished_jobs/show/'.$proId.'?content_format=html&business_id='.$_scripted_business_id.'&key='.$apiKey);             
            $_projectContent = json_decode($_projectContent);
            if($_projectContent->id == $proId) {
                
                $content = $_projectContent->content;
                if(is_array($content)) {
                    $content = $content[0];
                }
                
                $post['post_title']     = wp_strip_all_tags($_projectContent->topic);
                $post['post_status']    = 'draft';
                $post['post_author']    = $userID;
                $post['post_type']      = 'post';
                $post['post_content']   = $content;
				$post['post_content']   .= '<p style="font-style:italic; font-size: 10px;">Powered by <a href="https://Scripted.com" alt="Scripted.com content marketing automation">Scripted.com</a></p>';
                $post_id = wp_insert_post($post ,true);
                echo 'Draft Created!';
				$track_url = 'http://toofr.com/api/track?url='.urlencode(get_permalink($post_id)).'&title='.urlencode($post['post_title']);
				@file_get_contents($track_url);
            }
            
        } else {
            echo 'You are not authorized.';
        }
        
    }
    die();
}
add_action('wp_ajax_create_scripted_project', 'createScriptedProject');
function createProjectAjax()
{
    ?>
    <script>
        function createProject(id) {
            var proId = jQuery('#project_'+id).val();
                    jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo wp_nonce_url( admin_url('admin-ajax.php'), 'create_project' );?>',
                            data: 'proId='+proId+'&action=create_scripted_project',
                            success: function(data) {
                                 jQuery("#create_"+proId).html(data); 

                        }
                        });
       }
    </script>
        <?php
}