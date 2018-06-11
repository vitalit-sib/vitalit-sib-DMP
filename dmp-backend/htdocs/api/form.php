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
// get the necessery information to create the formulary in the frontend (whit the list of the previous forms)
function getForm($loggedLogin,$loggedCode){
	
	$user_info = DB::queryFirstRow("SELECT user_id,institution from users where login = %s and code = %s",$loggedLogin,$loggedCode);
	$institution = getUserInstitution($loggedLogin,$loggedCode);	
	$form = array();
	
	$about_contact = DB::queryFirstRow("SELECT institution_name, about, contact from institutions where institution_name =%s",$institution);
	
	
	
	
	if(!isset($about_contact["about"])){
		$about_contact["about"]= DB::queryFirstField("SELECT about  from institutions where institution_name = 'default.ch'");
	}
	
	if(!isset($about_contact["contact"])){
		$about_contact["contact"]= DB::queryFirstField("SELECT contact  from institutions where institution_name = 'default.ch'");
	}
	$form["about"] = $about_contact["about"];
	$form["contact"] = $about_contact["contact"];
	
	$parameter_types = DB::query("SELECT parameter_type_id, type, has_parent,has_other from parameter_types where has_parent = 'N'");
	foreach($parameter_types as $type_idx => $parameter_type){

		if(!isset($form[$parameter_type['type']])){
			$has_other = ($parameter_type['has_other']=='Y');
			$form[$parameter_type['type']] = array('has_other' => $has_other,'params' => array());
			if($has_other){
				$form[$parameter_type['type']]["other"] = array('is_selected' => false,'value' => '','array' => array('index' => 0 , 'value' => ''));
			}
		}

		$recommended = DB::queryFirstField("SELECT recommendation_id from cv_recommendations where recommendation ='recommended'");
		
		
		$parameters = DB::query("SELECT   parameters.parameter_id,parameters.abbreviation, parameters.name, parameters.description, parameters.link, parameters.dependent_parameter_id, parameters.value, parameters.idx,parameters.is_selected,parameters.key_name,recommendation
FROM   parameters            
       left join institution_recommendations as included_parameters on `included_parameters`.`parameter_id` = `parameters`.`parameter_id` and included_parameters.`institution_name` = %s and included_parameters.`recommendation_id` > 1
       
       left join cv_recommendations on included_parameters.`recommendation_id` = `cv_recommendations`.`recommendation_id`
       
       left join institution_recommendations as excluded_parameters on `excluded_parameters`.`parameter_id` = `parameters`.`parameter_id` and excluded_parameters.`institution_name` != %s and excluded_parameters.`recommendation_id` = 3      
where excluded_parameters.parameter_id is null  and parameter_type_id = %i",$institution,$institution,$parameter_type["parameter_type_id"]);
		
		foreach($parameters as $idx => $parameter){
			
			
			if($parameter['recommendation'] == "recommended"){
				
				
				$parameter['is_recommended'] = 'Y';
				
			} 
			else {$parameter['is_recommended'] = 'N';} 
			$parameter['is_selected'] = ($parameter['is_selected'] != 'N');
			$parameter['is_recommended'] = ($parameter['is_recommended'] != 'N');
			
			unset($parameter['recommendation']);

			$values = DB::query("SELECT parameters.parameter_id, parameters.abbreviation, parameters.name, parameters.description, parameters.link, parameters.is_selected,parameter_types.type as parameter_type, parameters.dependent_parameter_id,parameters.value,parameters.key_name,'N' as is_recommended from parameters inner join parameter_types on parameters.parameter_type_id = parameter_types.parameter_type_id where parent_parameter_id = %i order by parameter_types.type asc, parameters.idx asc, parameters.abbreviation asc ",$parameter['parameter_id']);
			

			foreach($values as $value){
				if(!isset($parameter[$value['parameter_type']])){
					$parameter[$value['parameter_type']] = array();
				}
				$value['is_selected'] = ($value['is_selected'] != 'N');
				$value['is_recommended'] = ($value['is_recommended'] != 'N');


				$recommendation = DB::queryFirstRow("SELECT 'Y' as 'is_recommended' from institution_recommendations where parameter_id = %i and recommendation_id = %i and institution_name =%s",$value['parameter_id'],$recommended ,$user_info['institution']);

				if(!empty($recommendation)){
					$recommendation= ($value['is_recommended'] != 'N');

					$value['is_recommended'] = $recommendation;
				}
				else{
					$value['is_recommended']='false';
				}





				$parameter[$value['parameter_type']][] = $value;
			}
			$form[$parameter_type['type']]['params'][] = $parameter;
		}
	}
	$form['previousTemplate'] =DB::query("SELECT dmp_name,date,dmp_id from dmps where user_id = %i ORDER BY date DESC ",$user_info['user_id']);

	$form['internal_existing_data']=array('use' => FALSE,'has_other' => TRUE, 'params' => array_map(function($a){
		if(isset($a['repositories'])) unset($a['repositories']);
		if(isset($a['metadata'])) unset($a['metadata']);
		return $a;
	},$form['datatypes']['params']),"other"=>array('is_selected'=> FALSE,'value'=>"",'array' => array('index' => 0 , 'value' => '')));

	$form['external_existing_data']=array('use' => FALSE,'has_other' => TRUE, 'params' => array_map(function($a){
		if(isset($a['repositories'])) unset($a['repositories']);
		if(isset($a['metadata'])) unset($a['metadata']);
		return $a;
	},$form['datatypes']['params']),"other"=>array('is_selected'=> FALSE,'value'=>"",'array' => array('index' => 0 , 'value' => '')));

	return $form;

}

// give the correct file path to the downlodResult function
function getReadyForDownload($user_id,$code){
	$dir = DATA_PATH."/dmp_".$user_id;

	$filePath = $dir."/".$code.'.docx';

	downloadResult($filePath);
}

// function to dowload the generated file
function downloadResult($file){

	$dir = dirname($file);

	//First, see if the file exists
	if (!is_file($file)) { throw new Exception("ERROR: file not found", 501);
	}

	//Gather relevent info about file
	$len = filesize($file);
	$filename = 'DMP.docx';
	$ctype="application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	//Begin writing headers
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");

	//Use the switch-generated Content-Type
	header("Content-Type: $ctype");
	//Force the download
	$header='Content-Disposition: attachment; filename="'.$filename.'";';
	header($header );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$len);
	@readfile($file);

	unlink($file);
	// 	rmdir($dir);
	exit;

}


// delete previously filled form
function deleteTemplate($template_id,$loggedLogin,$loggedCode){


	$UserId = DB::queryFirstField("SELECT user_id from users where login =%s and code=%s",$loggedLogin,$loggedCode);

	if($UserId && isset($UserId)){
		DB::delete('dmps', "dmp_id=%i", $template_id);
	}
	else{

		throw new Exception("Permission denied", 501);
	}

}
// load  previously filled form
function getPreviousTemplate($dmp_id){

	$parameters = DB::query("SELECT * from parameter_types right join dmp_parameters on parameter_types.parameter_type_id = dmp_parameters.parameter_type_id where dmp_id =%i",$dmp_id);
	$previous_form = array();

	$parameter_ids = array();
	foreach($parameters as $param){
		if(isset($param['parameter_id'])){

			if(!isset($param['existing_data'])){

				$param_info = DB::queryFirstRow("SELECT abbreviation,name,description,link,dependent_parameter_id,parent_parameter_id from parameters where parameter_id = %i",$param['parameter_id']);

				$complementary_param_info = array('parameter_id'=> $param['parameter_id'],'value' => $param['value'], 'is_selected' => TRUE );
				$parameter_ids[]= $param['parameter_id'];
				$selected_param=array_merge($complementary_param_info, $param_info);
				$previous_form[$param['type']]['params'][]= $selected_param;
			}

		}

		else{
			$previous_form[$param['type']]['has_other'] = TRUE;

			$previous_form[$param['type']]['other']= array('value' => $param['value'], 'is_selected' => TRUE );
		}

	}

	$missing_param_info = DB::query("SELECT false as is_selected, parameters.parameter_id,abbreviation,name,description,link,dependent_parameter_id,has_other,parent_parameter_id, parameter_types.type as parameter_type  from parameters
		right join parameter_types on  parameters.parameter_type_id = parameter_types.parameter_type_id
	where parameter_id not in %li ",$parameter_ids);
	foreach($missing_param_info as $missing){


		$has_other = ($missing['has_other']=='Y');
		$previous_form[$missing['parameter_type']]['has_other'] = $has_other;
		if($has_other){
			$previous_form[$missing['parameter_type']]["other"] = array('is_selected' => false,'value' => '');
			unset($missing['has_other']);
		}
		$previous_form[$missing['parameter_type']]['params'][] = $missing;

	}
	foreach($previous_form['datatypes']['params'] as $key => $datatype){

		foreach($previous_form['metadata']['params'] as $metadata){
			if($datatype['parameter_id'] == $metadata['parent_parameter_id']){

				$previous_form['datatypes']['params'][$key]['metadata'][] = $metadata;
			}
		}
		foreach($previous_form['repositories']['params'] as $repo){
			if($datatype['parameter_id'] == $repo['parent_parameter_id']){
				$previous_form['datatypes']['params'][$key]['repositories'][] = $repo;
			}

		}
	}
	$previous_form['external_existing_data'] =existing_data('external_existing_data',$previous_form,$dmp_id);
	$previous_form['internal_existing_data'] =existing_data('internal_existing_data',$previous_form,$dmp_id);


	return $previous_form;

}

// create and the internal and/or external existing to data the previous template loaded by the user
function existing_data($existing_data,$previous_form,$dmp_id){
	$existing_data_type = explode('_',$existing_data)[0];
	$previous_form[$existing_data] = $previous_form['datatypes'];

	$data = DB::query("SELECT parameter_id, value from dmp_parameters where existing_data =%s and dmp_id=%i",$existing_data_type,$dmp_id);
	$previous_form[$existing_data]['has_other'] = FALSE;

	foreach($previous_form[$existing_data]['params'] as $key =>  $int){

		foreach($data as $temp_int){
			if($temp_int['parameter_id'] == $int['parameter_id']){

				$previous_form[$existing_data]['params'][$key]['is_selected'] = TRUE;
			}
			else{
				$previous_form[$existing_data]['params'][$key]['is_selected']= FALSE;
			}

			if(isset($temp_int['value'])){
				$previous_form[$existing_data]['has_other'] = TRUE;

				$previous_form[$existing_data]['other']['is_selected'] =  TRUE;
				$previous_form[$existing_data]['other']['value'] = $temp_int['value'];
			}


		}
	}
	return $previous_form[$existing_data] ;
}

?>