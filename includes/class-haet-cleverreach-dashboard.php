<?php

class haet_cleverreach_dashboard {
	
    function admin_page_scripts_and_styles($page){
    	if($page=='index.php'){
    		wp_enqueue_script('haet_cleverreach_dashboard_chart',  HAET_CRD_URL.'/js/Chart.min.js', array( ));
	        wp_enqueue_script('haet_cleverreach_dashboard_admin_script',  HAET_CRD_URL.'/js/admin_script.js', array( 'jquery-ui-dialog','jquery', 'haet_cleverreach_dashboard_chart'));
	        $cached_data = get_option('haet_crd_cache');
	        wp_localize_script( 'haet_cleverreach_dashboard_admin_script', 'ajax_object',
	                    array( 
	                    	'ajax_url' 	=> admin_url( 'admin-ajax.php' ),
	                    	'str_loading'	=> __( 'refreshing data...', 'haet_cleverreach_dashboard' ),
	                    	'str_connecting'=> __( 'connecting to CleverReach...', 'haet_cleverreach_dashboard' ),
	                    	'haet_crd_cached_data'=>json_encode($cached_data),
	                    	) );
	        wp_enqueue_style('haet_cleverreach_dashboard_admin_style',  HAET_CRD_URL.'/css/style.css');
	        wp_enqueue_style (  'wp-jquery-ui-dialog');
	        wp_enqueue_media();
	    }
    }
	 
	function setup_widget() {
		global $wp_meta_boxes;

		wp_add_dashboard_widget('haet_cleverreach_dashboard', __('CleverReach','haet_cleverreach_dashboard'), array($this,'print_widget'));
	}

	function print_widget() {
		$api_key = get_option('haet_crd_api_key');
		$test_result = $this->test_api_key($api_key);
		if( !$test_result['success'] )
			include HAET_CRD_PATH.'views/admin/settings.php';
		else
			include HAET_CRD_PATH.'views/admin/widget-small.php';

	}


	function save_settings() {
		if(isset($_POST['api_key'])){
			$api_key = $_POST['api_key'];
			$test_result = $this->test_api_key($api_key);
			if($test_result['success']){
				update_option('haet_crd_api_key',$api_key);
				echo 'success';
			}else
				echo '<p class="error">'.$test_result['message'].'</p>';
		}
		wp_die(); 
	}

	/**
	 * Test API Key (valid / write-access)
	 *
	 * @return array
	 */
	private function test_api_key($api_key=null){
		if(!$api_key){
			$test_result['success']=false;
			$test_result['message']=__( 'Please enter your CleverReach API key.', 'haet_cleverreach_dashboard' );
			return $test_result;
		}

		$test_result=array();
		$api = new SoapClient(HAET_CRD_API_URL);
		try{
			$result = $api->groupGetList($api_key);
			if($result->status=="SUCCESS"){
				$test_result['success']=true;
				$test_result['message']=__( 'We successfully connected to Cleverreach with your API key.', 'haet_cleverreach_dashboard' );
			}else{
				$test_result['success']=false;
				$test_result['message']=__( 'Your API key is invalid.', 'haet_cleverreach_dashboard' );
			}
		} catch(Exception $e){
			$test_result['success']=false;
			$test_result['message']=__( 'Your API key is invalid.', 'haet_cleverreach_dashboard' );
		}
		return $test_result;
	}


	/**
	 * Retreive latest 30 reports from cleverreach API
	 *
	 * @return array
	 */
	function get_chart_data(){
		$api_key = get_option('haet_crd_api_key');
		if( !$api_key ){
			$return['success']=false;
			$return['message']=__( 'Please enter your API key on the Cleverreach config page.', 'haet_cleverreach_dashboard' );
			echo json_encode($return);
			wp_die();
		}

		$api = new SoapClient(HAET_CRD_API_URL);
		try{
			$result = $api->reportGetDetailedList($api_key);
			if($result->status=="SUCCESS"){
				if( !is_array($result->data) ){
					$return['success']=false;
					$return['message']=__( 'You don\'t have any CleverReach reports.', 'haet_cleverreach_dashboard' );
				}else{
					$return['success']=true;
					$return['reports']=array();
					$latest_report_id = $result->data[0]->id;
					$data = array_reverse($result->data);
					$opened_pc = 0;
					$clicked_pc = 0;
					$bounced_pc = 0;
					$unopened_pc = 0;
					$report_index = 0;
					foreach( $data AS $cr_report ){
						// colors http://colrd.com/palette/28419/
						$is_latest = false;
						if( $report_index++==count($data)-1 )
							$is_latest=true;

						$opened = $cr_report->opened - $cr_report->clicks;
						$new_opened_pc = round(100*$opened/$cr_report->receiver_count,0);
						if($opened_pc!=0){
							$opened_diff = $new_opened_pc - $opened_pc;
							$opened_diff = '('.($opened_diff>0?'+':'').$opened_diff.' %)';
						}else
							$opened_diff='';
						$opened_pc=$new_opened_pc;

						$clicked = $cr_report->clicks;
						$new_clicked_pc = round(100*$clicked/$cr_report->receiver_count,0);
						if($clicked_pc!=0){
							$clicked_diff = $new_clicked_pc - $clicked_pc;
							$clicked_diff = '('.($clicked_diff>0?'+':'').$clicked_diff.' %)';
						}else
							$clicked_diff='';
						$clicked_pc=$new_clicked_pc;

						$bounced = $cr_report->bounced;
						$new_bounced_pc = round(100*$bounced/$cr_report->receiver_count,0);
						if($bounced_pc!=0){
							$bounced_diff = $new_bounced_pc - $bounced_pc;
							$bounced_diff = '('.($bounced_diff>0?'+':'').$bounced_diff.' %)';
						}else
							$bounced_diff='';
						$bounced_pc=$new_bounced_pc;

						$unopened = $cr_report->receiver_count - $cr_report->bounced - $cr_report->opened;
						$new_unopened_pc = round(100*$unopened/$cr_report->receiver_count,0);
						if($unopened_pc!=0){
							$unopened_diff = $new_unopened_pc - $unopened_pc;
							$unopened_diff = '('.($unopened_diff>0?'+':'').$unopened_diff.' %)';
						}else
							$unopened_diff='';
						$unopened_pc=$new_unopened_pc;

						$return['reports'][] = array(
								'name'			=> $cr_report->name,
								'sent'			=> date_i18n( get_option( 'date_format' ),$cr_report->sent),
								'chart_data' 	=> array(
										array(
											'value'		=>	$opened,
											'color'		=>	'rgba(44, 76, 0, 1)',
											'highlight'	=>	'rgba(67, 101, 0, 1)',
											'label'		=>	$opened_pc.' % '.($is_latest?__( 'opened', 'haet_cleverreach_dashboard' ).$opened_diff:''),
											),
										array(
											'value'		=>	$clicked,
											'color'		=>	'rgba(102, 153, 0, 1)',
											'highlight'	=>	'rgba(153, 204, 0, 1)',
											'label'		=>	$clicked_pc.' % '.($is_latest?__( 'opened & clicked', 'haet_cleverreach_dashboard' ).$clicked_diff:''),
											),
										array(
											'value'		=>	$bounced,
											'color'		=>	'rgba(204, 0, 0, 1)',
											'highlight'	=>	'rgba(255, 68, 68, 1)',
											'label'		=>	$bounced_pc.' % '.($is_latest?__( 'bounced', 'haet_cleverreach_dashboard' ).$bounced_diff:''),
											),
										array(
											'value'		=>	$unopened,
											'color'		=>	'rgba(0, 114, 153, 1)',
											'highlight'	=>	'rgba(0, 153, 204, 1)',
											'label'		=>	$unopened_pc.' % '.($is_latest?__( 'unopened', 'haet_cleverreach_dashboard' ).$unopened_diff:''),
											),
									)
							);
					}
					$return['reports'] = array_reverse($return['reports']);
					$details = $api->reportGetDetails($api_key, $latest_report_id, 1);
					$details = new SimpleXMLElement($details->data->xml);
					//$return['details'] = $details;
					$return['daily_chart_data']=array(
							'labels'	=>	array(),
							'datasets'	=>	array(
								array(
									'label'			=> __( 'opened', 'haet_cleverreach_dashboard' ),
									'fillColor'		=> 'rgba(67, 101, 0, 0.2)',
									'strokeColor'	=> 'rgba(67, 101, 0, 1)',
									'pointColor'	=> 'rgba(44, 76, 0, 0.5)',
									'pointStrokeColor' => 'rgba(44, 76, 0, 1)',
									'pointHighlightFill' => '#fff',
									'pointHighlightStroke' => 'rgba(220,220,220,1)',
									'data'	=> array()
									),
								array(
									'label'			=> __( 'clicks', 'haet_cleverreach_dashboard' ),
									'fillColor'		=> 'rgba(153, 204, 0, 0.6)',
									'strokeColor'	=> 'rgba(153, 204, 0, 1)',
									'pointColor'	=> 'rgba(102, 153, 0, 0.5)',
									'pointStrokeColor' => 'rgba(153, 204, 0, 1)',
									'pointHighlightFill' => '#fff',
									'pointHighlightStroke' => 'rgba(220,220,220,1)',
									'data'	=> array()
									),
								)
						);
					foreach ($details->stats->daily->item as $day) {
						$return['daily_chart_data']['labels'][] = date( 'm-d',intval($day->stamp));
						$return['daily_chart_data']['datasets'][0]['data'][] = intval($day->total_opened);
						$return['daily_chart_data']['datasets'][1]['data'][] = intval($day->total_clicks);
					}

					$links=array();
					// if I assign $links=$details->links->clicklink it does  $links=$details->links->clicklink[0]
					// WHY!!!????
					// This is just a workaround!
					foreach ($details->links->clicklink as $link){
						$links[]=$link;
					}

					usort($links,array($this,'compare_links'));

					$return['links']='
						<table class="haet-crd-links">
							<tr>
								<th class="link-name" colspan="2">'.__( 'Link URL', 'haet_cleverreach_dashboard' ).'</th>
								<th class="link-clicks">'.__( 'Clicks', 'haet_cleverreach_dashboard' ).'</th>
							</tr>';
					$link_order=1;
					foreach ($links as $link){
						if($link_order==11 )
							break;
						$link_name = $link->display_name;
						$link_name = str_replace('http://www.', '', $link_name);
						$link_name = str_replace('https://www.', '', $link_name);
						$link_name = str_replace('http://', '', $link_name);
						$link_name = str_replace('https://', '', $link_name);
						$link_name = (strlen($link_name)<50 ? $link_name : substr($link_name, 0,45).'...');
						$return['links'].='
							<tr class="'.($link_order%2==0?'even':'odd').'">
								<td class="link-number">'.$link_order.'</td>
								<td class="link-name">'.$link_name.'</td>
								<td class="link-clicks">'.(intval($link->clicks) ? $link->clicks : 0).'</td>
							</tr>';
						$link_order++;
					}
					$return['links'].='</table>';

				}
			}else{
				$return['success']=false;
				$return['message']=$result->message;
			}
		} catch(Exception $e){
			$return['success']=false;
			$return['message']=__( 'Could not connect to the Cleverreach API.', 'haet_cleverreach_dashboard' );
		}

		if($return['success']==true)
			update_option('haet_crd_cache',$return);

		echo json_encode($return);
		wp_die();
	}

	/**
	*	Helper function for usort to order top links by click
	**/
	function compare_links($link_a,$link_b){
		if(!isset($link_a->clicks))
			$link_a->clicks=0;
		if(!isset($link_b->clicks))
			$link_b->clicks=0;

		if (intval($link_a->clicks) == intval($link_b->clicks)) {
	        return 0;
	    }
	    return (intval($link_a->clicks) > intval($link_b->clicks)) ? -1 : 1;
	}
}


?>