<?php

//make shortcodes from functions in functions.php
add_shortcode('tsml_group_count', 'tsml_group_count');
add_shortcode('tsml_location_count', 'tsml_count_locations');
add_shortcode('tsml_meeting_count', 'tsml_count_meetings');
add_shortcode('tsml_region_count', 'tsml_count_regions');

//function for shortcode: get a table of the next $count meetings
if (!function_exists('tsml_next_meetings')) {
    function tsml_next_meetings($arguments)
    {
        global $tsml_program, $tsml_programs;
        $arguments = shortcode_atts(array('count' => 5), $arguments, 'tsml_next_meetings');
        $meetings = tsml_get_meetings(array('day' => intval(current_time('w')), 'time' => 'upcoming'));
        if (!count($meetings)) {
            return false;
        }

        //usort($meetings, 'tsml_next_meetings_sort');
        $meetings = array_slice($meetings, 0, $arguments['count']);
        $rows = '';
        foreach ($meetings as $meeting) {
            if (is_array($meeting['types'])) {
                $flags = array();
                foreach ($tsml_programs[$tsml_program]['flags'] as $flag) {
                    if (in_array($flag, $meeting['types'])) {
                        $flags[] = $tsml_programs[$tsml_program]['types'][$flag];
                    }
                }
                if (count($flags)) {
                    sort($flags);
                    $meeting['name'] .= '<small>' . implode(', ', $flags) . '</small>';
                }
            }
            $rows .= '<tr>
				<td class="time">' . tsml_format_time($meeting['time']) . '</td>
				<td class="name"><a href="' . $meeting['url'] . '">' . @$meeting['name'] . '</a></td>
				<td class="location">' . @$meeting['location'] . '</td>
				<td class="region">' . (@$meeting['sub_region'] ? @$meeting['sub_region'] : @$meeting['region']) . '</td>
			</tr>';
        }
        return '<table class="tsml_next_meetings table table-striped">
			<thead>
				<tr>
					<th class="time">' . __('Time', '12-step-meeting-list') . '</th>
					<th class="name">' . __('Meeting', '12-step-meeting-list') . '</th>
					<th class="location">' . __('Location', '12-step-meeting-list') . '</th>
					<th class="region">' . __('Region', '12-step-meeting-list') . '</th>
				</tr>
			</thead>
			<tbody>' . $rows . '</tbody>
		</table>';
    }
}
add_shortcode('tsml_next_meetings', 'tsml_next_meetings');

//output a list of types with links for AA-DC
if (!function_exists('tsml_types_list')) {
    function tsml_types_list()
    {
        global $tsml_types_in_use, $tsml_programs, $tsml_program;
        $types = array();
        $base = get_post_type_archive_link('tsml_meeting') . '?tsml-day=any&tsml-type=';
        foreach ($tsml_types_in_use as $type) {
            $types[$tsml_programs[$tsml_program]['types'][$type]] = '<li><a href="' . $base . $type . '">' . $tsml_programs[$tsml_program]['types'][$type] . '</a></li>';
        }
        ksort($types);
        return '<h3>Types</h3><ul>' . implode($types) . '</ul>';
    }
}
add_shortcode('tsml_types_list', 'tsml_types_list');

//output a list of regions with links for AA-DC
if (!function_exists('tsml_regions_list')) {
    function tsml_regions_list()
    {
        //run function recursively
        function get_regions($parent = 0)
        {
            $taxonomy = 'tsml_region';
            $terms = get_terms(compact('taxonomy', 'parent'));
            if (!count($terms)) {
                return;
            }

            $base = get_post_type_archive_link('tsml_meeting') . '?tsml-day=any&tsml-region=';
            foreach ($terms as &$term) {
                $term = '<li><a href="' . $base . $term->term_id . '">' . $term->name . '</a>' . get_regions($term->term_id) . '</li>';
            }
            return '<ul>' . implode($terms) . '</ul>';
        }

        return '<h3>Regions</h3>' . get_regions();
    }
}
add_shortcode('tsml_regions_list', 'tsml_regions_list');

function page_getallnew( $atts ) {
        global $wpdb;
        echo "<style>@media screen AND (min-width:600px){.Rtable{display:flex!important;flex-wrap:wrap;margin:0 0 0 0;padding:0}#navbars{text-align:center}.navbar select{display:none}section{padding:15px 15px 35px 15px}.footer >div >ul{display:flex;list-style-type:none;align-items:center;flex-direction:row;justify-content:center}.Rtable-cell{box-sizing:border-box;flex-grow:1;width:100%;padding:0.2em .4em;overflow:hidden;list-style:none;border:1px solid white;background:fade(slategrey,20%)}.Rtable-cell2{box-sizing:border-box;flex-grow:1;width:100%;padding:0.2em .4em;overflow:hidden;list-style:none;border:1px solid white;background:fade(slategrey,20%)}.Rtable--2cols > .Rtable-cell{width:50%}.Rtable--3cols > .Rtable-cell{width:33.33%}.Rtable--4cols > .Rtable-cell{width:25%}.Rtable--5cols > .Rtable-cell{width:20%}.Rtable--6cols > .Rtable-cell{width:16.6%}.Rtable--6cols > .Rtable-cell2{width:33.2%!important}}@media screen AND (max-width:600px){.Rtable{display:block;border:2px solid #000;border-radius:4px;margin:5px}.Rtable-cell{text-align:center}}</style>";
    	    $mtAr = tsml_get_meetings();
    	    // Obtain a list of columns
            foreach ($mtAr as $key => $row) {
                if ($row['day'] != '0') {
                    $day[$key]  = $row['day'];
                } else {
                    $row['day'] = '7';
                    $day[$key]  = $row['day'];
                }
                $loc[$key] = $row['region'];
                $time[$key] = $row['time'];
            }
            // Sort the data 
            array_multisort($day, SORT_ASC, $loc, SORT_ASC, $time,SORT_ASC, $mtAr);
            $holder="";
            echo "<section id='tables'>";
            $holder= new meeting();
            $current_region = '';
    	    foreach ($mtAr as $i) {
    	        echo var_dump($i);  // to test
    	        $types = "";
    	        foreach ($i['types'] as $t) {
    	            $types.= $t."; ";
    	        }
    	        /** preferred codes
    	        $types = str_replace("LGBTQ","G",$types);
    	        $types = str_replace("SP","S",$types);
    	        $types = str_replace("X","WA",$types); 
    	        **/
    	        
    	        $dowMap = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
                if ($holder->get()!=$dowMap[$i['day']]) {
                        echo '<hr id="'.$holder->get().'" class="sep" /><h2 style="text-align: left;" id="'.$holder->get().'" class="'.$holder->get().'">'.$holder->get().'</h2><hr class="sep" />'; 
					} 
                   
				$adNumber = explode(",",$i['formatted_address']);

				if ($i['location'] != $current_region) {
					echo "<h5 style='margin-top: 15px;'>".$i['region']."</h5>";
					$current_region = $i['region'];
				}
				echo '<div class="Rtable Rtable--6cols"><div class="Rtable-cell first" class="'.$dowMap[$i['day']].'"><a href="https://hacoaa.org/tsml_region/'.str_replace(' ','-',strtolower($i['region'])).'">'.$i['region'].'</a></div><div class="Rtable-cell"><!--<a class="easy-modal-open" href="#modal2" rel="nofollow" onclick="modalCreate(\"'.$dowMap[$i['day']].";;".$i['time_formatted'].";;".$i['name'].";;".str_replace(' ','+',$i['formatted_address']).'");"><i class="material-icons" style="font-size:24px;">place</i></a>-->'.' '.$i['time_formatted'].'</div>
				<div class="Rtable-cell"><a href='.$i['url'].'><strong>'.$i['name'].'</strong></a>';
				global $current_user;
				wp_get_current_user();
				if($current_user->ID == '1'||$current_user->ID == '3') {
					echo ' <a href="/wp-admin/post.php?post='.$i['id'].'&action=edit">edit</a>';
				}
				echo  '</div><div class="Rtable-cell">'.$types.'</div>
						<div class="Rtable-cell cell2"><a href='.$i['location_url']."'>".$i['location'].": ".$adNumber[0].'</a></div>
						</div>';
                
                $holder->set($dowMap[$i['day']]);
                if ($holder->get()!=$dowMap[$i['day']]) {
                    echo '</div>';
                }    
    	    }
            echo "</section>";
}
add_shortcode('tsml_meetingtable', 'page_getallnew');
class meeting {
    var $day = 'Mon';
    
    public function set($mt_day){
        $this->day = $mt_day;
    }
    
    public function get() {
        return $this->day;
    }
}
