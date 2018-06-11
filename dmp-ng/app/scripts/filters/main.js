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


'use strict';

/**
 * @ngdoc filter
 * @name dmpApp.filter:splitDot
 * @function
 * @description
 * # splitDot
 * Filter in the dmpApp. Adds a _<br>_ tag after each '. '
 */
angular.module('dmpApp')
  .filter('splitDot', splitDot)
  .filter('formatMimeType', formatMimeType)
  .filter('humanSamples', humanSamples)
  .filter('recommended', recommended)
  .filter('noSubtitles', noSubtitles)
  .filter('sortObj',sortObj)
  .filter('formatNot',formatNot);

  function splitDot() {
      return function (input) {
  		return input.replace(/\.\s+/g,".<br>");
      };
   }

   function formatMimeType(){
	   return function(type){
		   return type.replace('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',"Excel").replace("application/vnd.openxmlformats-officedocument.wordprocessingml.document",'Word').replace('text/plain','txt').replace('application/vnd.openxmlformats-officedocument.spre','Excel').replace('application/vnd.oasis.opendocument.spreadsheet','OpenDocument Spreadsheet').replace('application/vnd.oasis.opendocument.presentation',' Open Document Format');
	   }
   }

   function humanSamples(){
	   return function(samples){
		   var human_samples = [];
		   angular.forEach(samples,function(sample){
			   if (sample.name.toLowerCase().indexOf('human')>-1) human_samples.push(sample);
		   });
		   return human_samples;
	   }
   }

   function recommended(){
	   return function(is_recommended){
		   return ;
	   }
   }
function noSubtitles(){
  return function(items){
    var sections = [];
    angular.forEach(items,function(item,idx){
      if (item.title != null) sections.push(item);
    });
    return sections;
  }
}

	function  sortObj() {
		return function(obj){
			var obj_ordered = {};
			Object.keys(obj).sort().forEach(function(key) {
			  obj_ordered[key] = obj[key];
			});
		   return obj_ordered;
	   }
	}


	function formatNot(){
		return function(text){
			if (text.substr(0,4).toLowerCase() !== 'not_') return text;
			if (text.indexOf(' not ') > -1){
				return text.substr(4).replace(" not "," ");
			}
			if (text.indexOf('We have') > -1){
				return text.substr(4).replace("We have","We don't have");
			}
			return "No "+text.substr(4,1).toLowerCase()+text.substr(5);
			return text;
		}
	}
