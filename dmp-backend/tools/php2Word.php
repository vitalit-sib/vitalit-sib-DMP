<?php

// create the header for the mean document , if present add the project name and its description.
function dmp_header($request){
	$headers = array();
	$headers[] = "<h1>".$request['name']."</h1>";
	$headers[] = "<p>".$request['description']."</p>";
	return implode("\n",$headers);
}
// get the selected parameters
function get_selected_parameters($request){

	$sub_types = DB::queryFirstColumn("SELECT type from parameter_types where has_parent = 'Y'");

	$selected_parameters = array();
	$selected_abbreviation = array();
	if(!is_array($request)) $request = (array) $request;
	foreach($request as $parameter_type_name => $parameter_type){
		if(isset($parameter_type['params']) && strpos($parameter_type_name,'existing_data') === FALSE){
			foreach($parameter_type['params'] as $parameter){
				if($parameter['is_selected']){
					$selected_parameters[] = $parameter['parameter_id'];
					$selected_abbreviation[] = $parameter['abbreviation'];
					foreach($sub_types as $sub_type){
						if(isset($parameter[$sub_type])){
							foreach($parameter[$sub_type] as $param){
								if($param['is_selected'] && !in_array($param['parameter_id'],$selected_parameters)){
									$selected_parameters[] = $param['parameter_id'];
									$selected_abbreviation[] = $param['key_name'];
									
								} 
							}
						}
					}
				}
			}
		}
	}
	
	return array('parameter_ids' => $selected_parameters, 'parameter_key_names' =>$selected_abbreviation );
}

// remplace variable starting with {:: by the value selected/enter by the user   
function replace_categories($text,$request){
	if(strpos($text,'{::') === FALSE) return $text;
	preg_match_all("/\{::([^\}]+)\}/",$text,$outs);
	foreach($outs[1] as $type){
		$find = "{::".$type."}";
		$replaces = array();
		if(isset($request[$type])){
			foreach($request[$type]['params'] as $field){
				if($field['is_selected']) $replaces[] = $field['name'];
			}
			if(isset($request[$type]['other']) && $request[$type]['other']['is_selected']){
				$values = explode(",",$request[$type]['other']['value']);
				foreach($values as $value) $replaces[] = trim($value);
			}
		}
		$replaces = array_filter($replaces);
		$last = array_pop($replaces);

		$replace = implode(", ",$replaces);
		$replace .= ($replace) ? " and ".$last : $last;
		$text = str_replace($find,$replace,$text);



	}
	return $text;
}

// the data parameters contain values enter by the user which can not go to other categories
//remplace the variable starting by {:data: by the value enter by the user in form 
function replace_data_parameters($text,$request){
	if(strpos($text,'{:data:') === FALSE) return $text;
		
	preg_match_all("/\{:data:([^\}]+)\}/",$text,$outs);
	foreach($outs[1] as $type){
		$find = "{:data:".$type."}";
		$replace = "";
		foreach($request['data']['params'] as $param){
			if($param['abbreviation'] == $type && $param['is_selected'] && isset($param['value'])){
				$replace = $param['value'];
			}
		}
		$text = str_replace($find,$replace,$text);
	}
	return $text;
}

// replace the variable of the internal and/or external existing data 
function replace_existing_data($text,$request){
	$categories = array('internal','external');

	foreach($categories as $category){
		if(strpos($text,'{:'.$category.'_existing_data') !== FALSE){

			$find = "{:".$category."_existing_data}";
			$replaces = array();

			if(isset($request[$category.'_existing_data'])){
				foreach($request[$category.'_existing_data']['params'] as $datatype){
					if($datatype['is_selected']) $replaces[] = $datatype['name'];
				}
				if(isset($request[$category.'_existing_data']['other']) && $request[$category.'_existing_data']['other']['is_selected']){
					$values = explode(",",$request[$category.'_existing_data']['other']['value']);
					foreach($values as $value) $replaces[] = trim($value);
				}
			}

			$last = array_pop($replaces);

			$replace = implode(", ",$replaces);
			$replace .= ($replace) ? " and ".$last : $last;
			$text = str_replace($find,$replace,$text);
		}

	}
	return $text;
}
// display or not the variable text in the header/footer
function display_variable_header_footer($text,$selected_parameters_abbreviation){	
	if(strpos($text,"{{") !== FALSE){
		if(preg_match_all("/\{\{(\!?)param:(\w+)\ (.*?)\}\}/",$text,$matches,PREG_SET_ORDER)){
	
			foreach($matches as $part){
				if((in_array($part[2],$selected_parameters_abbreviation) && $part[1] == '!') || (!in_array($part[2],$selected_parameters_abbreviation) && !$part[1])){
					$text = str_replace($part[0],'',$text);
				}
				else{
					$text = str_replace($part[0],$part[3],$text);
				}
			}

		}	
	}
	

	return $text;
	
	
}
// convert the created text to an html format
function text2html($text){
	$paragraphs = explode("\n",$text);
	return implode("\n",array_map(function($p){return "<p>".$p."</p>";},$paragraphs));
}
// function to create the section of the document
function print_section($request,$main_section,$selected_parameters,$institution){
	$request = dependent_parameter_to_false($request);
	$contents = array();

	
	$selected_parameter_ids = $selected_parameters['parameter_ids'];
	$selected_parameters_abbreviation = $selected_parameters['parameter_key_names'];
	$level = substr_count($main_section['section_number'],".")+2;
	$contents[] = "";
	if($main_section['title']) $contents[] = "<h$level>".$main_section['section_number']." ".$main_section['title']."</h$level>";

	if($main_section['heading']){
			$main_section['heading'] = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$main_section['heading']);
			$main_section['heading'] = display_variable_header_footer($main_section['heading'],$selected_parameters_abbreviation);
			$contents[] = "<p>".text2html($main_section['heading'])."</p>";
	} 
	
	$template_paremeter_institu_specific= DB::query("SELECT institution_templates.template_id, parameter_id from institution_templates
	inner join templates on institution_templates.template_id = templates.template_id
	 where institution_name = %s",$institution);
	 
	 $institu_all_templates_ids = DB::queryOneColumn('template_id', "SELECT * FROM institution_templates");
	 $institu_spec_templates_ids = array();
	 $institu_spec_parameters_ids = array();
	 
	 foreach($template_paremeter_institu_specific as $templateID_parameterID){

	 	$institu_spec_templates_ids[]=$templateID_parameterID['template_id'];
		$institu_spec_parameters_ids[]=$templateID_parameterID['parameter_id'];
	 }
	 
	 
	 $templates= get_templates($institution,$main_section['section_number']);
	 
	 
	 unset($institu_templates_ids);
	 unset($institu_spec_parameters_ids);
	 foreach($templates as $template){
		if(in_array($template['parameter_id'],$selected_parameter_ids)){

			if($template['dependent_parameter_id'] && !in_array($template['dependent_parameter_id'],$selected_parameter_ids)) continue;

			$parameter = DB::queryFirstRow("SELECT * from parameters where parameter_id = %i",$template['parameter_id']);
			
			//replace variable cointains in the text matching the $field
			$find = (array_map(function($field){return "{:$field}";},array_keys($parameter)));
			$replace = array_values($parameter);
			$text = str_replace($find,$replace,$template['text']);


			$subparameters = DB::query("SELECT parameter_types.`type` as type, group_concat(abbreviation order by parameters.parameter_id separator ', ') as abbreviation,  group_concat(name order by parameters.parameter_id  separator ', ') as name,group_concat(description order by parameters.parameter_id  separator ', ') as description,group_concat(link order by parameters.parameter_id  separator ', ') as link from parameters inner join parameter_types on parameters.parameter_type_id = parameter_types.parameter_type_id where parameters.parent_parameter_id = %i and parameters.parameter_id in %li group by parameters.parameter_type_id",$template['parameter_id'], $selected_parameter_ids);
			
			
			
			
			foreach($subparameters as $subparameter){
				//replace variable cointains in the text matching the $field if matching the subparameter( parameter_type)
				$find = array_keys($subparameter);
				array_walk($find,function(&$field,$key,$subparameter){$field = "{:".$subparameter['type'].":".$field."}";},$subparameter);
				$replace = array_values($subparameter);
				$text = str_replace($find,$replace,$text);
			}
			$text = trim($text,'.');
			$text = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$text);
			$text = "<p>" . str_replace("\n", "</p><p>", $text) . ".</p>";
			
			
			$contents[] = "<p>".$text."</p>";
			
		}
	}

	// Check if 'other' has been selected and append 'generic' templates
	$parameter_type = DB::queryFirstField("SELECT parameter_types.type,count(templates.template_id) as nb from templates inner join parameters on templates.parameter_id = parameters.parameter_id inner join parameter_types on parameters.parameter_type_id = parameter_types.parameter_type_id where section_number = %s group by parameter_types.type order by nb desc limit 1;",$main_section['section_number']);


	if($parameter_type && isset($request[$parameter_type])  && isset($request[$parameter_type]['other'])){
		if($request[$parameter_type]['other']['is_selected'] && $request[$parameter_type]['other']['value']){
			
			$generic_template = DB::queryFirstRow(" SELECT templates.text,templates.template_id, institution_templates.text as institution_text from templates 
  inner join institution_templates on templates.template_id = institution_templates.template_id
  where section_number = %s and parameter_id is NULL and not_parameter_id is NULL and institution_name = %s ; ",$main_section['section_number'], $institution);
 
			  
			  if(isset($generic_template['institution_text'])) $generic_template = $generic_template['institution_text'];
			  else{ $generic_template = DB::queryFirstField("SELECT text from templates where section_number = %s and parameter_id is NULL and not_parameter_id is NULL",$main_section['section_number']);};
			
			$values = explode(",", $request[$parameter_type]['other']['value']);
			foreach($values as $value){
				$text = str_replace("{:name}",$value,$generic_template);
				$text = trim($text,'.');
				$text = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$text);
				$text = "<p>" . str_replace("\n", "</p><p>", $text) . ".</p>";
				
				
				$contents[] = "<p>".$text."</p>";
			}
		}
	}

	$templates = DB::queryFirstColumn("SELECT text from templates where parameter_id is NULL and not_parameter_id is NOT NULL and not_parameter_id not in %li and section_number = %s",$selected_parameter_ids,$main_section['section_number']);

	foreach($templates as $text){
		$text = trim($text,'.');
		$text = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$text);
		$text = "<p>" . str_replace("\n", "</p><p>", $text) . ".</p>";
		
		
		$contents[] = "<p>".$text."</p>";
	}

	// $sections = DB::query("SELECT * from sections where section_number like '".$main_section['section_number'].".%' order by section_number asc");
	
	$sections = get_sections($institution,$main_section['section_number']);
	
	foreach($sections as $section){
		$reg = "/^".$main_section['section_number']."\.\d+$/";

		if(!preg_match($reg,$section['section_number'])){
			continue;
		}

		$contents[] = print_section($request,$section,$selected_parameters,$institution);
	}

	if($main_section['footer']){
		$text = text2html(trim($main_section['footer'],'.'));
		$text = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$text);
		$text = display_variable_header_footer($text,$selected_parameters_abbreviation);
		
		
		$contents[] = "<p>".$text."</p>";
	}

	$content = implode("\n\n",$contents);
	$content = replace_categories($content,$request);
	$content = replace_data_parameters($content,$request);
	$content = replace_existing_data($content,$request);
	$content = preg_replace("/(\[[^\]]+\])/",'<code>$1</code>',$content);
	
	return $content;

}
// main function, create the document
function create_html_doc($request,$loggedLogin,$loggedCode){
	$contents = array();
	
	$institution = DB::queryFirstField("SELECT institution from users where login=%s and code=%s",$loggedLogin,$loggedCode);
	// $main_sections = DB::query("SELECT * from sections where section_number not like '%.%' order by section_number asc");
	$main_sections = get_main_sections($institution);
	$selected_parameters = get_selected_parameters($request);
	
	$contents[] = dmp_header($request);
	
	foreach($main_sections as $main_section){
		$contents[] = print_section($request,$main_section,$selected_parameters,$institution);
	}
	$html =  implode("\n\n\n",$contents);
	
	// $html = preg_replace("/(https?:\/\/[^\s]+)/",'<a href="$1">$1</a>',$html);
	
	
	


	$user_id = DB::queryFirstField("SELECT user_id from users
	where code =%s and login =%s", $loggedCode,$loggedLogin);
	
	

	$code = html2docx($html,$user_id,$request);
	return $code;
}
// convert the html file to a docx thanks to PANDOC (https://pandoc.org/)
function html2docx($html,$user_id,$request){
	$tmpfname = tempnam("/tmp", "FOO").".html";
	file_put_contents($tmpfname,$html);
	$dir = DATA_PATH."/dmp_".$user_id;
	if(!file_exists($dir)) mkdir($dir,0755,true);

	exec(PANDOC_PATH." -s --reference-docx ".PANDOC_REFERENCE." -f html -t docx -o ".$dir."/DMP.docx ".$tmpfname);
	if(file_exists($dir."/DMP.docx")){
		$code = rand_str();
		rename($dir."/DMP.docx",$dir."/".$code.".docx");
		save_dmp_in_db($user_id,$request);
		return $code;
	}
	else throw new Exception("ERROR: sorry, the docx file could not be created.", 501);

}
// save the dmp in the database
function save_dmp_in_db($user_id,$request){

	DB::insert('dmps', array(
		'user_id' => $user_id,
		'dmp_name' => $request['name'],
		'dmp_description' => $request['description']
	));
	$dmp_id = DB::insertId();	
	$dmp_content = array();
	$dmp_content['dmp_id'] = $dmp_id;

	foreach($request as $parameter_type_name => $parameters_type ){
		if(!isset($parameters_type)) continue;
		if(isset($parameters_type['has_other']) && $parameters_type['has_other'] === TRUE){
			if($parameters_type['other']['is_selected'] === TRUE){
				$dmp_content = insertAndClean($dmp_content,NULL,$parameters_type['other']['value'],NULL,$parameter_type_name);
			}			
		}
		if (is_array($parameters_type) || is_object($parameters_type)){
			foreach($parameters_type as $params){
				if (is_array($params) || is_object($params)){
					foreach($params as $param){
						if(isset($param['is_selected']) && $param['is_selected'] === TRUE){					
							if($parameter_type_name == 'external_existing_data' || $parameter_type_name == 'internal_existing_data'){
								$existing_data = explode("_",$parameter_type_name)[0];								
								$dmp_content = insertAndClean($dmp_content,$param['parameter_id'],$param['value'],$existing_data);
							}
					
							else{
								$dmp_content = insertAndClean($dmp_content,$param['parameter_id'],$param['value']);
							}

						}
						if(isset($param['metadata'])){
							foreach($param['metadata'] as $metadata){
								if($metadata['is_selected'] === TRUE){
									$dmp_content = insertAndClean($dmp_content,$metadata['parameter_id'],$metadata['value']);
								}
							}
						}
						if(isset($param['repositories'])){
							foreach($param['repositories'] as $repositorie){
								if($repositorie['is_selected'] === TRUE){
								}
							}
						}

					}
				}

			}
		}
		

	}
}
// insert the formulary parameters in the database 
function insertAndClean($dmp_content,$param_id,$value,$existing_data = NULL,$parameter_type_name = NULL){
	if(isset($param_id)){
		$dmp_content['parameter_type_id'] = DB::queryFirstField("SELECT parameter_type_id from parameters where parameter_id =%i",$param_id);
	}
	if(isset($parameter_type_name)){
				
		$parameter_type_id = DB::queryFirstField("SELECT parameter_type_id from parameter_types where type = %s",$parameter_type_name);
		
		
		if(isset($parameter_type_id)) {$dmp_content['parameter_type_id'] = $parameter_type_id ;} 
		
		else{$dmp_content['parameter_type_id'] = NULL ;
			$existing_data = explode("_",$parameter_type_name)[0];
		}
		
	}
	
	
	$dmp_content['parameter_id'] = $param_id;
	$dmp_content['value'] = $value;
	$dmp_content['existing_data'] = $existing_data;
	DB::insert('dmp_parameters',$dmp_content);
	
	$dmp_content['parameter_id']  = '';
	$dmp_content['value'] = '' ;
	$dmp_content['parameter_type_id'] = '';
	return $dmp_content;
	
}

// change to false the depedent paremeter id if the main parameter is false
function dependent_parameter_to_false($request){
	if(isset($request['data']['params'])){
		foreach($request['data']['params'] as  $param){
			if($param['is_selected'] === FALSE){
				
				
				foreach($request as $parameter_types_key =>  $parameter_types){
					if(isset($parameter_types['params'])){
						foreach($parameter_types['params'] as $parameter_key =>  $parameter){
				
							if(isset($request[$parameter_types_key]['params'])){
								if($parameter['is_selected']=== TRUE  && $parameter['dependent_parameter_id']== $param['parameter_id']){
									$request[$parameter_types_key]['params'][$parameter_key]['is_selected'] = false;

								}
							
							}
						
						}
					}
				}
			}
		}
	}
	return $request;
}

function get_main_sections($institution){
	$institution_sections = DB::queryFirstColumn("SELECT section_number  from institution_sections where institution_name = %s and  section_number not like '%.%'  order by section_number",$institution);
	if(!empty($headers_footers_institutions))
	{	
		
			
		$main_sections = DB::query("SELECT section_number  from institution_sections where institution_name = %s and  section_number not like '%.%' union SELECT section_number from sections where section_number not in %li  and  section_number not like '%.%'    order by section_number asc",$institution,$institution_sections);
		// $main_sections = array_merge($headers_footers_institutions, $headers_footers);
	}
	else
	{	
		$main_sections = DB::query("SELECT * from sections where section_number not like '%.%' order by section_number asc");
		
	}
	return $main_sections;
	
}
function get_sections($institution,$main_section_number){
	$sections = DB::query("SELECT * from sections where section_number like '".$main_section_number.".%' order by section_number asc");

	$headers_footers_institutions = DB::query("SELECT * from  institution_sections where institution_name = %s and  section_number like '".$main_section_number.".%' order by section_number asc",$institution);
	if(!empty($headers_footers_institutions)){
		$institution_sections = array();
		foreach($headers_footers_institutions as $headers_footers_institution)
		{
			$institution_sections[] = $headers_footers_institution["section_number"];
		}
		
		$sections = DB::query("SELECT section_number, title, heading, footer from  institution_sections where institution_name = %s and  section_number like '".$main_section_number.".%' union
			SELECT * from sections where section_number like '".$main_section_number.".%' and section_number not in %ls  order by section_number asc",$institution,$institution_sections);

	}



	else{
		$sections = DB::query("SELECT * from sections where section_number like '".$main_section_number.".%' order by section_number asc");

	}

	return $sections;
	
	
};

function get_templates($institution,$section){
	
	
	$templates_institution = DB::queryFirstField("SELECT institution_templates.template_id from institution_templates where institution_name =%s",$institution);

	if(!empty($templates_institution)){
		
		//AVEC NOUVELLE STUCTURE:
		
		$texts = DB::query("SELECT  templates.template_id,section_number,templates.parameter_id,institution_templates.text,not_parameter_id,parameters.dependent_parameter_id,idx,abbreviation from templates
		left join parameters on templates.parameter_id = parameters.parameter_id
		right join institution_templates on templates.template_id = institution_templates.template_id where section_number = %s and institution_name = %s
		
union	
		

SELECT  templates.*, parameters.dependent_parameter_id,idx,abbreviation from templates
		left join parameters on templates.parameter_id = parameters.parameter_id
		left join institution_templates on templates.template_id = institution_templates.template_id where section_number = %s and templates.template_id not in  ( SELECT  templates.template_id from templates right join institution_templates on templates.template_id = institution_templates.template_id where section_number = %s and institution_name = %s) ORDER BY  section_number,case when idx is null then 1 else 0 end,idx,abbreviation",$section,$institution,$section,$section,$institution);
		
	
	}
	else{
	
		$texts = DB::query("SELECT templates.*, parameters.dependent_parameter_id,idx from templates left join parameters on templates.parameter_id = parameters.parameter_id where  templates.section_number =%s ORDER BY  templates.section_number,case when idx is null then 1 else 0 end,idx,abbreviation",$section);
		
		
	}
	return $texts;
	
	
	
	
}
?>