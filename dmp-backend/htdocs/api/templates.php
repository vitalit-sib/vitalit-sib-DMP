<?php
/************************ LICENCE ***************************
*     This file is part of <DMP Canvas Generator web application>
*     Copyright (C) <2016> SIB Swiss Institute of Bioinformatics
*
*     This program is free software: you can redistribute it and/or modify
*     it under the terms of the GNU Affero General Public License as
*     published by the Free Software Foundation, either version 3 of the
*     License, or (at your option) any later version.
*
*     This program is distributed in the hope that it will be useful,
*     but WITHOUT ANY WARRANTY; without even the implied warranty of
*     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*     GNU Affero General Public License for more details.
*
*     You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>
*
*****************************************************************/
function getAlltemplates($loggedLogin,$loggedCode){
	
	$institution = getUserInstitution($loggedLogin, $loggedCode);
	
	$templates_institution = DB::query("SELECT institution_templates.template_id, section_number,parameter_id from institution_templates inner join templates on templates.template_id = institution_templates.template_id where institution_name =%s",$institution);
	$recommendation_exclusive_id = DB:: queryFirstField("SELECT cv_recommendations.recommendation_id from cv_recommendations inner join institution_recommendations on cv_recommendations.recommendation_id = institution_recommendations.recommendation_id where recommendation = 'exclusive' and institution_name != %s",$institution);



	if(!empty($templates_institution)){

		
		
		$texts = DB::query("SELECT templates.*,
       institution_templates.`text` as text_institution,
       parameters.*,
       title,
       `cv_recommendations`.`recommendation`
FROM   templates
       LEFT JOIN institution_templates
              ON templates.template_id = institution_templates.template_id
                 AND institution_templates.`institution_name` = %s
       left join parameters on templates.parameter_id = parameters.parameter_id
       left join sections on  templates.section_number = sections.section_number
       left join institution_recommendations as included_parameters on `included_parameters`.`parameter_id` = `parameters`.`parameter_id` and included_parameters.`institution_name` = %s and included_parameters.`recommendation_id` > 1
       left join cv_recommendations on included_parameters.`recommendation_id` = `cv_recommendations`.`recommendation_id`
       left join institution_recommendations as excluded_parameters on `excluded_parameters`.`parameter_id` = `parameters`.`parameter_id` and excluded_parameters.`institution_name` != %s and excluded_parameters.`recommendation_id` = 3      
where excluded_parameters.parameter_id is null 
ORDER  BY  templates.section_number,case when idx is null then 1 else 0 end,idx,abbreviation",$institution,$institution,$institution);
		
	
		
	}
	else{

		$texts = DB::query("SELECT  templates.section_number,text,name,not_parameter_id,title,templates.parameter_id,template_id,not_parameter_id,institution_name,recommendation_id  from templates left join parameters on templates.parameter_id = parameters.parameter_id left join sections on  templates.section_number = sections.section_number left join institution_recommendations on institution_recommendations.parameter_id = parameters.parameter_id  where   (recommendation_id !=%i or recommendation_id is null)   ORDER BY  templates.section_number,case when idx is null then 1 else 0 end,idx,abbreviation;",$recommendation_exclusive_id);


	}
	

	
	
	$headers_footers_institutions = DB::query("SELECT section_number,heading,footer from institution_sections where institution_name = %s",$institution);
	if(!empty($headers_footers_institutions))
	{	

		
		$headers_footers = DB::query("SELECT sections.section_number,sections.heading,sections.footer,institution_sections.heading as heading_institution,institution_sections.footer as footer_institution  from sections left join institution_sections on sections.section_number = institution_sections.section_number where institution_name = %s or institution_name is null",$institution);
	
	}
	else
	{	
		$headers_footers =  DB::query("SELECT section_number,heading,footer from sections");
	}
	
	
	$templates = array();
	foreach($texts as $text){
		$templates[$text['section_number']]['title']= $text['title'];
		$templates[$text['section_number']]['section_number']= $text['section_number'];
		$length_section_number = sizeof(explode(".",$text['section_number']));
		
		$section_number_parent  = implode('.', array_slice(explode('.', $text['section_number']), 0, $length_section_number-1));
		$templates[$text['section_number']]['section_number_parent']= $section_number_parent;
		
		
		if(!isset($text['text'])) $text['text']=  null;
		
		if(isset($text['text_institution'])) $text['text_user'] = $text['text_institution'];
		else{$text['text_institution'] = null;
			$text['text_user'] = $text['text'];}
		
		
		if($text['name'] != NULL){
			
			// $templates[$text['section_number']]['text'][$text['name']]= $text['text'];
			$templates[$text['section_number']]['templates'][$text['name']]['parameter_name']= $text['name'];
			$templates[$text['section_number']]['templates'][$text['name']]['parameter_id']= $text['parameter_id'];
			$templates[$text['section_number']]['templates'][$text['name']]['not_parameter_id']= $text['not_parameter_id'];	
			$templates[$text['section_number']]['templates'][$text['name']]['template_id']= $text['template_id'];
			$templates[$text['section_number']]['templates'][$text['name']]['text']= $text['text'];
			$templates[$text['section_number']]['templates'][$text['name']]['text_institution']= $text['text_institution'];
			$templates[$text['section_number']]['templates'][$text['name']]['text_user']= $text['text_user'];
					
		}
		else{
			
			if(isset($text['not_parameter_id'])){
				$complementary_param = DB::queryFirstField("SELECT name from parameters where parameter_id =%i", $text['not_parameter_id']);
				if(isset($complementary_param)){
					// $templates[$text['section_number']]['text']['not_'.$complementary_param]= $text['text'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['parameter_name']= 'not_'.$complementary_param;
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['parameter_id']= $text['parameter_id'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['not_parameter_id']= $text['not_parameter_id'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['template_id']= $text['template_id'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['text']= $text['text'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['text_institution']= $text['text_institution'];
					$templates[$text['section_number']]['templates']['not_'.$complementary_param]['text_user']= $text['text_user'];
					

				}

			}
			
			else{
				// $templates[$text['section_number']]['text']['Question or general sentence']= $text['text'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['Question or general sentence']= $text['name'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['parameter_id']= $text['parameter_id'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['not_parameter_id']= $text['not_parameter_id'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['template_id']= $text['template_id'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['text']= $text['text'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['text_institution']= $text['text_institution'];
				$templates[$text['section_number']]['templates']['Question or general sentence']['text_user']= $text['text_user'];
				
			}
		}
	
	}
	
	foreach($templates as $section_part =>$template)
	{
		
		foreach($headers_footers as $h_f)
		{
			if($section_part == $h_f['section_number'])
			{	
				
				if(!isset($h_f['heading'])) $h_f['heading']=  null;
				if(!isset($h_f['heading_institution'])) $h_f['heading_institution']=  null;
				if(!isset($h_f['heading_user'])) $h_f['heading_user']=  null;
				if(!isset($h_f['footer'])) $h_f['footer']=  null;
				if(!isset($h_f['footer_institution'])) $h_f['footer_institution']=  null;
				if(!isset($h_f['footer_user'])) $h_f['footer_user']=  null;
				
				
				if($h_f['heading_institution']) $h_f['heading_user'] = $h_f['heading_institution'];
				else{$h_f['heading_user'] = $h_f['heading'];}
				if($h_f['footer_institution']) $h_f['footer_user'] = $h_f['footer_institution'];
				else{$h_f['footer_user'] = $h_f['footer'];}
				
				$templates[$section_part]['heading'] = $h_f['heading'];
				$templates[$section_part]['heading_institution'] = $h_f['heading_institution'];
				$templates[$section_part]['heading_user'] = $h_f['heading_user'];
				$templates[$section_part]['footer'] = $h_f['footer'];
				$templates[$section_part]['footer_institution'] = $h_f['footer_institution'];
				$templates[$section_part]['footer_user'] = $h_f['footer_user'];
				
			
			}
		}
	}
	
	return $templates;
	
	
}

function createTemplateSpecific($request,$loggedLogin,$loggedCode){
	
	$is_editor = check_editor($loggedLogin, $loggedCode);
	if($is_editor == 'N')throw new Exception("Permission denied", 501);
	
	$institution = getUserInstitution($loggedLogin,$loggedCode);
	if($institution == "default.ch")$default = true; 
	 
	else{
		$default = false; 
	
	}
	
	foreach($request as $sections => $templates)
	{
		foreach($templates as $part => $templ)
		{
			if(isset($templates['heading_user']) && isset($templates['footer_user'])){
				
				
				if(trim($templates['heading_user']) == trim($templates['heading']) &&  trim($templates['footer_user']) == trim($templates['footer']) ){
					
					if(!$default)DB::delete('institution_sections', "section_number=%s and institution_name = %s",$sections,$institution);
					
				}
				
			}
				
			if($part == "heading_user" || $part == "footer_user" ){
				$insert = TRUE;
				
				if($part == "heading_user"){
					
					
					if(trim($templates['heading_user']) == trim($templates['heading'])) $insert = FALSE;
					createSection($templates['heading_user'],$loggedLogin,$loggedCode,'heading',$sections,$insert,$default);
				}
				if($part == "footer_user"){
					if(trim($templates['footer_user']) == trim($templates['footer'])) $insert = FALSE;
					
					createSection($templates['footer_user'],$loggedLogin,$loggedCode,'footer',$sections,$insert,$default);
				}
			}
			if($part == "templates"){
				foreach($templ as $parameter)
				{	
					
					if($parameter['text_user']){
							$delete = FALSE;
							if(trim($parameter['text_user']) == trim($parameter['text'])) $delete =TRUE;
							
							if(!$delete and isset($parameter['template_id']) and $default ){
								DB::update('templates', array( 'text' => $parameter['text_user']  ), "template_id=%i", $parameter['template_id']);
								
							}
							$institution_text_id = DB::queryFirstField(" SELECT template_id from institution_templates where template_id =%i and institution_name = %s",$parameter['template_id'],$institution);
							
							if($delete and isset($institution_text_id)){
								if(!$default){DB::delete('institution_templates', "template_id=%i and institution_name = %s", $parameter['template_id'],$institution);}
									
								
							} 
							elseif(isset($institution_text_id))DB::update('institution_templates', array( 'text' => $parameter['text_user']  ), "template_id=%i and institution_name = %s", $parameter['template_id'],$institution);
							elseif(!$delete){
							
								if(!$default){
									DB::insert('institution_templates', array(
										  'template_id' => $parameter['template_id'],
										  'institution_name' => $institution,
										  'text' => $parameter['text_user']
										));
									
								}
							
							}
 
						
					}
					
					
				}
			}
		}
		
	}
	
	
	return TRUE;
}
function createSection($template,$loggedLogin,$loggedCode,$heading_or_footer,$sections,$insert,$default){
	$is_editor = check_editor($loggedLogin, $loggedCode);
	if($is_editor == 'N')throw new Exception("Permission denied", 501);	
	$institution = getUserInstitution($loggedLogin,$loggedCode);
	
	$section_number = DB::queryFirstField("SELECT section_number from institution_sections where institution_name = %s  and section_number =%s",$institution,$sections);

	if($default){
		if($heading_or_footer == 'heading'){
		
			DB::update('sections', array(
			  'heading' => $template,
			  ), "section_number=%s ",$sections);
		}
		if($heading_or_footer == 'footer'){
			DB::update('sections', array(
			  'footer' => $template,
			  ), "section_number=%s" ,$sections );
		  }
		
		
		
	}
	else{
		
		if(isset($section_number))
		{	
		
			if($heading_or_footer == 'heading'){
				
				DB::update('institution_sections', array(
				  'heading' => $template,
				  ), "section_number=%s and institution_name = %s",$section_number,$institution );
			}
			if($heading_or_footer == 'footer'){
				DB::update('institution_sections', array(
				  'footer' => $template,
				  ), "section_number=%s and institution_name = %s",$section_number,$institution );
			}
		
		
		}
		else
		{	
		
  	  
			$title = DB::queryFirstField("SELECT title from sections where  section_number = %s",$sections);
		
			if($heading_or_footer == 'heading')
			{
				if($template && $insert)
				{
			
					DB::insert("institution_sections",array(
					'section_number' => $sections,
					'title' => $title,
					'heading' => $template,
					'institution_name' => $institution

					));

				}

			}
			if($heading_or_footer == 'footer')
			{
				if($template && $insert){
					DB::insert("institution_sections",array(
					'section_number' => $sections,
					'title' => $title,
			  		  'footer' => $template,
			  		  'institution_name' => $institution

					));

				}
			}
		
		}
		
	}
	
	
	return TRUE;
}
function getRecommendations($loggedLogin,$loggedCode){
	$recommendation = array();
	$institution = getUserInstitution($loggedLogin,$loggedCode);	
	$parameter_type_ids=DB::query("SELECT parameter_type_id from institution_recommended_parameter_types where institution_name = %s",$institution);
	if(empty($parameter_type_ids)){
		$parameter_type_ids=DB::query("SELECT parameter_type_id from institution_recommended_parameter_types where institution_name = %s",'default.ch');
		
	}
	
	$parameter_type_ids_list = array();

	foreach($parameter_type_ids as $id){
		$parameter_type_ids_list[]= $id['parameter_type_id'];
	}

	if(!empty($parameter_type_ids_list)){
		$recommended_parameters = DB::query("SELECT  parameters.parameter_id,abbreviation,name,institution_recommendations.recommendation_id,recommendation,type from parameters left join institution_recommendations on parameters.parameter_id = institution_recommendations.parameter_id left join cv_recommendations on institution_recommendations.recommendation_id = cv_recommendations.recommendation_id inner join parameter_types on parameters.parameter_type_id = parameter_types.parameter_type_id where parameters.parameter_type_id in %li ORDER BY idx,name", $parameter_type_ids_list);

		$parameters = array();

		foreach($recommended_parameters as $param){
			$parameters['parameter_types'][$param['type']][] = $param;
		}
		$recommendation[] = $parameters;

	}
	return $recommendation;

}

function addRecommendation($request,$loggedLogin,$loggedCode){
	$institution = getUserInstitution($loggedLogin,$loggedCode);
	$is_editor = check_editor($loggedLogin, $loggedCode);
	if($is_editor == 'N')throw new Exception("Permission denied", 501);

	foreach($request[0]['parameter_types'] as $parameter_types){
		foreach($parameter_types as $parameter){
			if(isset($parameter['recommendation_id'])){
				
				$recommendation = DB::queryFirstRow("SELECT recommendation_id,parameter_id from institution_recommendations where institution_name = %s and parameter_id = %i and recommendation_id = %i",$institution,$parameter['parameter_id'],$parameter['recommendation_id']);
			
				if(!empty($recommendation)){
					
				
					
					if($recommendation_id != $parameter['recommendation_id']){
						DB::update('institution_recommendations', array(
						  'recommendation_id' => $parameter['recommendation_id']
						  ), "institution_name = %s and parameter_id = %i ", $institution,$parameter['parameter_id']);
 
					}
				}
				else{
					DB::insert('institution_recommendations', array(
					  'institution_name' => $institution,
					  'parameter_id' => $parameter['parameter_id'],
					  'recommendation_id' => $parameter['recommendation_id']
					));
 
					
				}
			}
			else{
				
				$recommendation_to_delete = DB::queryFirstField("SELECT recommendation_id from institution_recommendations where institution_name = %s and parameter_id = %i ",$institution,$parameter['parameter_id']);
				
				
				if(isset($recommendation_to_delete)){
					
					DB::delete('institution_recommendations', "institution_name = %s and parameter_id = %i and recommendation_id = %i", $institution,$parameter['parameter_id'],$recommendation_to_delete);

				}
			
			}
		}
		
	}
}

function getParamKeyNames ($loggedLogin,$loggedCode){
	$is_editor = check_editor($loggedLogin, $loggedCode);
	if($is_editor == 'N')throw new Exception("Permission denied", 501);
	
	$paramKeyNames = DB::queryFirstColumn("SELECT key_name from parameters where text_in_variable = 'Y'");
	return $paramKeyNames;
}


function getAboutContact($loggedLogin,$loggedCode){
	$institution = getUserInstitution($loggedLogin,$loggedCode);
	$is_editor = check_editor($loggedLogin, $loggedCode);
	if($is_editor == 'N')throw new Exception("Permission denied", 501);
	
	$about_contact = DB::queryFirstRow("SELECT institution_name, about, contact from institutions where institution_name =%s",$institution);
	
	if(!isset($about_contact["about"])){
		$about_contact["about"]= DB::queryFirstField("SELECT about  from institutions where institution_name = 'default.ch'");
	}
	
	if(!isset($about_contact["contact"])){
		$about_contact["contact"]= DB::queryFirstField("SELECT contact  from institutions where institution_name = 'default.ch'");
	}
	
	$form = array();
	$form["about"] = $about_contact["about"];
	$form["contact"] = $about_contact["contact"];
	
	return [$form];
	
	
	
};
function addAboutContact($request,$loggedLogin,$loggedCode){
	$is_editor = check_editor($loggedLogin, $loggedCode);
	$institution = getUserInstitution($loggedLogin,$loggedCode);
	
	if($is_editor == 'N')throw new Exception("Permission denied", 501);
	$aboutContact = $request[0];
	DB::update('institutions', array(
	  'about' => $aboutContact['about'],
	  'contact' => $aboutContact['about']
	  ), "institution_name = %s  ", $institution);
	
	
};

?>