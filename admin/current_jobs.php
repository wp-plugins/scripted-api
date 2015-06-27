<?php

function scripted_create_current_jobs_callback()
{
    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');    
    
    $ID               = get_option( '_scripted_ID' );
    $accessToken      = get_option( '_scripted_auccess_tokent' );
    $paged            = (isset($_GET['paged']) and $_GET['paged'] !='') ? sanitize_text_field($_GET['paged']) : '';
    $per_page         = 15;    
    $validate = validateApiKey($ID,$accessToken);    
    $out = '<div class="wrap">
            <div class="icon32" style="width:100px;padding-top:5px;" id="icon-scripted"><img src="'.SCRIPTED_LOGO.'"></div><h2>Current Jobs <a class="add-new-h2" href="admin.php?page=scripted_create_a_job">Create a Job</a></h2>';
    
    if($validate) {
        $url = ($paged != '') ? 'jobs?next_cursor='.$paged : 'jobs/';
        $result = curlRequest($url);
        
        $allJobs = $result->data; 
        
        $next = (isset($result->paging->has_next) and $result->paging->has_next == 1) ? $result->paging->next_cursor : '';
        $totalProjects  = $result->total_count;
        $totalPages     = ceil($totalProjects/$per_page);
        
        // paggination
        
        $paggination = '';
        
         $pageOne = '';         
         if($paged == '' and $result->paging->has_next != 1)
             $pageOne = ' one-page';     
         
         $paggination .='<div class="tablenav">
            <div class="tablenav-pages'.$pageOne.'">';
         
                $paggination .='';
                $nextPage = '';
                if($result->paging->has_next != 1) 
                    $nextPage = 'disabled';
                
                $paggination .='<span class="pagination-links"> 
                            <span class="displaying-num">'.$totalProjects.' items</span>
                            <a href="admin.php?page=scripted_create_current_jobs&paged='.$next.'" title="Go to the next page" class="next-page '.$nextPage.'">&rsaquo;</a>';
         
                   $paggination .='</span>
             </div>
            <br class="clear">
            </div>';
        // paggination end
                   
        $out .= $paggination;
        
        $out .='<table cellspacing="0" class="wp-list-table widefat sTable">
                    <thead>
                        <tr>
                        <th scope="col" width="40%"><span>Topic</span></th>
                        <th scope="col" width="10%"><span>Quantity</span></th>
                        <th scope="col" width="10%"><span>State</span></th>
                        <th scope="col" width="15%"><span>Deadline</span></th>
                        <th scope="col" width="23%"></th>
                        </tr>
                    </thead>
                      <tbody>';
        
        if($allJobs)  {           
            $i = 1;
            foreach($allJobs as $job) {
                $out .='<tr valign="top" class="scripted type-page status-publish hentry alternate">
                    <input type="hidden" id="project_'.$i.'" value="'.$job->id.'">
                    <td>'.$job->topic.'</td>
                    <td>'.$job->quantity.'</td>
                    <td>'.ucfirst($job->state).'</td>
                    <td>'.date('F j', strtotime($job->deadline_at)).'</td>';
                
                    $out .='<td>';
                    if($job->state == 'ready for review') {
                        $out .= '<a id="accept_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Accept\')">Accept</a> | ';
                        $out .= '<a id="request_'.$job->id.'"  href="'.admin_url('admin-ajax.php').'?action=scripted_poject_finished&do=request_edit&project_id='.$job->id.'&secure='.wp_create_nonce('request_edit').'&amp;type=page&amp;TB_iframe=1&amp;width=600&amp;height=400" class="thickbox" title="'.strip_tags(substr($job->topic,0,50)).'">Request Edits</a>';
                    }elseif($job->state == 'ready for acceptance') {
                        $out .= '<a id="accept_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Accept\')">Accept</a> | ';
                        $out .= '<a id="reject_'.$job->id.'"  href="javascript:void(0)" onclick="finishedProjectActions(\''.$job->id.'\',\'Reject\')">Reject</a>';
                    }elseif ($job->state == 'accepted') {
                        $out .= '<a id="create_'.$job->id.'" href="javascript:void(0)"  onclick="finishedProjectActions(\''.$job->id.'\',\'Create\')">Create Draft</a> | ';
                        $out .= '<a id="post_'.$job->id.'" href="javascript:void(0)"  onclick="finishedProjectActions(\''.$job->id.'\',\'Post\')">Create Post</a> | ';
                        $out .= '<a href="'.admin_url('admin-ajax.php').'?action=scripted_poject_finished&do=view_project&project_id='.$job->id.'&secure='.wp_create_nonce('view_project').'&amp;type=page&amp;TB_iframe=1&amp;width=850&amp;height=500" class="thickbox" title="'.strip_tags(substr($job->topic,0,50)).'">View</a>';
                    }
                    $out .='</td>';
                    $out .='</tr>';
                    $i++;
            }
            
        } else {
            $out .='<tr valign="top">
                    <th colspan="5"  style="text-align:center;" class="check-column"><strong>Your Scripted account has no Current Jobs. <a href="admin.php?page=scripted_create_a_job">Create a Job</a></strong></td>
                    </tr>';
        }
        
         $out .= '</tbody>
                </table>'; // end table
        
       $out .= $paggination;
         
         
    }
    
    $out .='</div>';// end of wrap div
    
    echo $out;
}