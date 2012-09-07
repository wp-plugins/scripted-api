<?php

function scripted_create_current_jobs_callback()
{
    $apiKey                 = get_option( '_scripted_api_key' );
    $_scripted_business_id  = get_option( '_scripted_business_id' );
    $paged                  = (isset($_GET['paged']) and $_GET['paged'] !='') ? $_GET['paged'] : 1;
    $per_page               = 10;
    
    $validate = validateApiKey($apiKey,$_scripted_business_id);
    
    $out = '<div class="wrap">
            <div class="icon32" style="width:100px;padding-top:5px;" id="icon-scripted"><img src="'.SCRIPTED_LOGO.'"></div><h2>Current Jobs <a class="add-new-h2" href="admin.php?page=scripted_create_a_job">Create a Job</a></h2>';
    
    if($validate) {
        $_currentJobs = @file_get_contents('https://scripted.com/jobs?key='.$apiKey.'&business_id='.$_scripted_business_id.'&page='.$paged.'&per_page='.$per_page.'&');            
        $_currentJobs = json_decode($_currentJobs);
        
        $totalPagess  = $_currentJobs->total;
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
                
                $out .='<span class="pagination-links"><a href="admin.php?page=scripted_create_current_jobs&paged=1" title="Go to the first page" class="first-page  '.$prePage.'">&laquo;</a>
                        <a href="admin.php?page=scripted_create_current_jobs&paged='.$preLink.'" title="Go to the previous page" class="prev-page '.$prePage.'">&lsaquo;</a> 
                            <span class="paging-input">'.$paged.' of <span class="total-pages">'.$totalPages.'</span></span>
                            <a href="admin.php?page=scripted_create_current_jobs&paged='.$nextLink.'" title="Go to the next page" class="next-page '.$nextPage.'">&rsaquo;</a>
                        <a href="admin.php?page=scripted_create_current_jobs&paged='.$totalPages.'" title="Go to the last page" class="last-page '.$nextPage.'">&raquo;</a>';
         
                   $out .='</span>
             </div>
            <br class="clear">
            </div>';
        // paggination end
        
        $out .='<table cellspacing="0" class="wp-list-table widefat fixed pages">
                    <thead>
                        <tr>
                        <th style="" class="manage-column column-author" scope="col"><span>ID</span></th>
                        <th style="" class="manage-column column-author"scope="col"><span>Topic</span></th>
                        <th style="" class="manage-column column-author"scope="col"><span>State</span></th>
                        <th style="" class="manage-column column-author"scope="col"><span>Deadline</span></th>
                        </tr>
                    </thead>
                      <tbody id="the-list">
                    ';
        
        if($_currentJobs->total) {
            $currentJobs = $_currentJobs->jobs;
            
            foreach($currentJobs as $job) {
                $out .='<tr valign="top" class="scripted type-page status-publish hentry alternate">
                    <td class="author column-author">'.$job->id.'</td>
                    <td class="author column-author"><strong>'.$job->topic.'</strong></td>
                    <td class="author column-author">'.$job->state.'</td>
                    <td class="author column-author">'.$job->deadline_at.'</td>
                    </tr>';
            }
            
        } else {
            $out .='<tr valign="top" class="scripted type-page status-publish hentry alternate">
                    <th colspan="4"  style="text-align:center;" class="check-column"><strong>Your Scripted account has no Current Jobs. <a href="admin.php?page=scripted_create_a_job">Create a Job</a></strong></td>
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
                
                $out .='<span class="pagination-links"><a href="admin.php?page=scripted_create_current_jobs&paged=1" title="Go to the first page" class="first-page  '.$prePage.'">&laquo;</a>
                        <a href="admin.php?page=scripted_create_current_jobs&paged='.$preLink.'" title="Go to the previous page" class="prev-page '.$prePage.'">&lsaquo;</a> 
                            <span class="paging-input">'.$paged.' of <span class="total-pages">'.$totalPages.'</span></span>
                            <a href="admin.php?page=scripted_create_current_jobs&paged='.$nextLink.'" title="Go to the next page" class="next-page '.$nextPage.'">&rsaquo;</a>
                        <a href="admin.php?page=scripted_create_current_jobs&paged='.$totalPages.'" title="Go to the last page" class="last-page '.$nextPage.'">&raquo;</a>';
         
                   $out .='</span>
             </div>
            <br class="clear">
            </div>';
        // paggination end
         
         
    }
    
    $out .='</div>';// end of wrap div
    
    echo $out;
}