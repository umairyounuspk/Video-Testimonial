(function( $ ) {
	'use strict';

	var current_question_set = 0;
	var total_selectable = qts_object.selectable_questions;

	$.fn.AvRecorder = function (id, conf) {
		// Normalize features.
		navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia;
		window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext;
		window.URL = window.URL || window.webkitURL;
		
		// Feature detection.
		var getUserMediaCheck = typeof (navigator.getUserMedia || navigator.mediaDevices.getUserMedia) === 'function';
		var mediaRecorderCheck = typeof (window.MediaRecorder) === 'function';
		var webAudioCheck = typeof (window.AudioContext) === 'function';
	
		// Set recorder type.
		var recorderType = false;
		if (getUserMediaCheck && webAudioCheck && mediaRecorderCheck) {
			//Use browser based MediaRecorder API 
			recorderType = 'AvRecorder';  
		}
		else if (getUserMediaCheck && webAudioCheck && !mediaRecorderCheck) {
			//Fallback to use RecorderJS
			recorderType = 'AvRecorderHTML5';
		}
		
		console.log("Recording type = " + recorderType);
	
        var $avRecorder = $('#' + id);
        var $avRecorderFallback =  $('#' + id + '-fallback-ajax-wrapper');
        
        switch (recorderType) {
          case 'AvRecorder':
            $avRecorder.show();
            $avRecorderFallback.hide();
            new $('#' + id).AvRecorderMoz(id, conf);
            break;
          case 'AvRecorderHTML5':
            $avRecorder.show();
            $avRecorderFallback.hide();
            new $('#' + id).AvRecorderHTML5(id, conf);
            break;
          default:
            $avRecorder.hide();
        }
	}

	function createRecorder(){
		$("#avRecorder").AvRecorder('avRecorder',{
			constraints: {
				audio: true,
				video: true,
				video_resolution: "640"
			},
			file: null,
			time_limit: "180",
			server_upload_endpoint: qts_object
		});
	};

	$(document).ready(function(){
		// createRecorder();
		// var browserUserAgent = navigator.userAgent;
		// if(browserUserAgent.includes("Safari") == true && browserUserAgent.includes("Chrome") == false){
		// 	$("#audioVideoControl option")[1].remove();
		// }else if(browserUserAgent.includes("Edge") == true || browserUserAgent.includes("MSIE ") == true || browserUserAgent.includes("Trident") == true){
		// 	$("#audioVideoControl option")[1].remove();
		// }
		
		// $("#avRecorder").bind('uploadFinished', function (event, data) {
		// 	console.log("uploadFinished");
		// 	console.log(data);
		// });



		$('.qts-questions-wrapper input[type="checkbox"]').change( function(){
			let total_selected = $('.qts-questions-wrapper input[type="checkbox"]:checked').length;
			$("#qts-selected").html(total_selected);
			if(total_selectable == total_selected){
				$('.qts-control-info').removeClass('qts-hidden');
				$('.qts-save-btn').removeClass('qts-hidden');
				$('.qts-questions-wrapper input[type="checkbox"]:not(:checked)').attr('disabled','disabled');
			}else if( !$('.qts-control-info').hasClass('qts-hidden') ){
				$('.qts-control-info').addClass('qts-hidden');
				$('.qts-save-btn').addClass('qts-hidden');
				$('.qts-questions-wrapper input[type="checkbox"]').removeAttr('disabled');
			}
				
		});
		// Navigation - Next, Previous Questions Set
		$(".qts-nav-btn").on('click', function(event) {
			event.preventDefault();
			let question_sets = $('ul.qts-questions-container');
			
			if($(this).hasClass('next')){
				current_question_set++;
				
				if(question_sets.length-1 == current_question_set)
					$('.qts-nav-btn.next').addClass('qts-hidden');
				else
					$('.qts-nav-btn.prev').removeClass('qts-hidden');
			}else{
				current_question_set--;
				
				if(0 == current_question_set)
					$('.qts-nav-btn.prev').addClass('qts-hidden');
				else
					$('.qts-nav-btn.next').removeClass('qts-hidden');
			}		

			question_sets.addClass('qts-hidden');
			$(question_sets[current_question_set]).toggleClass('qts-hidden');			
		});

		$(".qts-save-btn").on('click', function(event){
			event.preventDefault();
			$(".qts-questions-wrapper").addClass("qts-hidden");
			
			let selected_questions = $('.qts-questions-wrapper input[type="checkbox"]:checked');
			let selected_questions_array = [];
			selected_questions.each(function() {
				selected_questions_array.push($(this).parent().find('label').text());
			});
			
			$('button.av-recorder-next').attr('data-questions', JSON.stringify(selected_questions_array));
			$('button.av-recorder-next').attr('current-question', '0');
			$('.av-recorder').prepend('<p class="qts-question"><b>Question (1 of '+total_selectable+'):</b> '+selected_questions_array[0]+'</p>');
			createRecorder();
		});


	});

})( jQuery );
