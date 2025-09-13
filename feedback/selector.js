		var minLength = 10;
		var maxLength = 60;

		$(document).ready(function(){

			/* https://makitweb.com/make-a-dropdown-with-search-box-using-jquery/ */

			// Initialize select2
			$("#journal").select2();

			$("#RRform").on("keypress", function (event) {

				var keyPressed = event.keyCode || event.which;

				if (keyPressed === 13) {
					event.preventDefault();
					return false;
				}
			});

			/* https://codepen.io/oomusou/pen/OMKZPZ */

			$('#agreeCheckbox').click(function () {
    			$('#submitbutton').prop("disabled", !$("#agreeCheckbox").prop("checked")); 
  			});
		});

		$("#reviewer_role").change(function() { 
	
			if ( $(this).val() != "0") {

				// show next part of questionnaire
				$("#journal_div").show();

				if ($("#journal").val() != "0") {

					$("#journal").trigger('change');
				}

				/*  check to ensure we cannot have an impossible situation
					i.e. user initially choses AUTHOR, picks a sub stage
					from Stage 1 and/or 2, then goes back and changes it to
					REVIEWER. Unfixed, this would a) likely confuse the user and
					b) potentially feed contradictory status data into our
					questionnaire generation function.
					
					We can guard against this by checking if the Stage select
					menu has already been chosen and hide the sub stage choices
					if the user chooses REVIEWER after earlier choosing AUTHOR */

				if ( $('#stage').val() != "0" ) {

					$('#stage_div').show();
					$('#stage1_div').hide();
					$('#stage2_div').hide();
					$('#stage1_role1_orphans_div').hide();
					$('#stage1_role2_orphans_div').hide();
					$('#stage2_role1_orphans_div').hide();
					$('#stage2_role2_orphans_div').hide();
					$('#stageboth_div').hide();
					$('#doi_div').hide();
					$('#s1y_div').hide();
					$('#s2y_div').hide();
					$('#s1role_div').hide();
					$('#s2role_div').hide();
					$('#submit_button_div').hide();
					$('#ref_code_div').hide();

					switch($('#stage').val()) {

						/* Stage 1 */

						case '1':

							if ($('#reviewer_role').val() == "1") {

								$('#stage1_div').show();
								$('#RRform input[name=stage1]').trigger('change');

								$('#stage1_role1_orphans_div').hide();
								$('#stage1_role2_orphans_div').hide();
								$('#stage2_role1_orphans_div').show();
								$('#stage2_role2_orphans_div').hide();
							}

							if ($('#reviewer_role').val() == "2") {

								$('#stage1_div').hide();
								$('#stage1_role1_orphans_div').hide();
								$('#stage1_role2_orphans_div').hide();
								$('#stage2_role1_orphans_div').hide();
								$('#stage2_role2_orphans_div').show();

								$('#s1y_div').show();
								$('#RRform input[name=s1year_choice]').trigger('change');
							}

							$('#stage2_div').hide();
							$('#stageboth_div').hide();

						break;

						/* Stage 2 */

						case '2':

							if ($('#reviewer_role').val() == "1") {

								$('#stage2_div').show();
								$('#RRform input[name=stage2]').trigger('change');

								$('#stage1_role1_orphans_div').show();
								$('#stage1_role2_orphans_div').hide();
								$('#stage2_role1_orphans_div').hide();
								$('#stage2_role2_orphans_div').hide();

								if ($("input[name='stage2']:checked").val() == "7") {

									$('#doi_div').show();
								}
							}

							if ($('#reviewer_role').val() == "2") {

								$('#stage2_div').hide();
								$('#stage1_role1_orphans_div').hide();
								$('#stage1_role2_orphans_div').show();
								$('#stage2_role1_orphans_div').hide();
								$('#stage2_role2_orphans_div').hide();

								$('#doi_div').hide();

								$('#s2y_div').show();
								$('#RRform input[name=s2year_choice]').trigger('change');
							}

							$('#stage1_div').hide();   						
							$('#stageboth_div').hide();

							if ($('#reviewer_role').val() == "1" && !$("input[name='stage2']:checked").val()) {

								$('#ref_code_div').hide();
								$('#submit_button_div').hide();
							}

						break;

						/* Stage 1 and 2 */

						case '3':

							$('#stage1_div').hide();
							$('#stage2_div').hide();

							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();

							if ($('#reviewer_role').val() == "1") {

								$('#stageboth_div').show();
								
								if ($("input:radio[name='stage1_both']").is(":checked") && $("input:radio[name='stage2_both']").is(":checked") ) {

									$('#s1y_div').show();
									$('#RRform input[name=s1year_choice]').trigger('change');

								}

								if ($("input[name='stage2_both']:checked").val() == "7") {

									$('#doi_div').show();
								}
							}

							if ($('#reviewer_role').val() == "2") {

								$('#s1y_div').show();
								$('#RRform input[name=s1year_choice]').trigger('change');
							}

							if ($('#reviewer_role').val() == "1" && (!$("input[name='stage1_both']:checked").val() || !$("input[name='stage2_both']:checked").val())) {

								$('#ref_code_div').hide();
								$('#submit_button_div').hide();
							}

						break;
					}
				}		
			}

			else

			{
				$('#journal_div').hide();
				$('#doi_div').hide();
				$('#stage_div').hide();

				$('#stage1_role1_orphans_div').hide();
				$('#stage1_role2_orphans_div').hide();
				$('#stage2_role1_orphans_div').hide();
				$('#stage2_role2_orphans_div').hide();

				$('#stage1_div').hide();
				$('#stage2_div').hide();
				$('#stageboth_div').hide();

				$('#s1y_div').hide();
				$('#s2y_div').hide();

				$('#s1role_div').hide();
				$('#s2role_div').hide();

				$("#doi_div").hide();

				$('#ref_code_div').hide();
				$('#submit_button_div').hide();
			}
		});


		$("#journal").change(function() { 
		
			if ( $(this).val() == "0") {

				$('#stage_div').hide();
				$('#stage1_div').hide();
				$('#stage2_div').hide();

				$('#stage1_role1_orphans_div').hide();
				$('#stage1_role2_orphans_div').hide();
				$('#stage2_role1_orphans_div').hide();
				$('#stage2_role2_orphans_div').hide();

				$('#stageboth_div').hide();
				$('#doi_div').hide();
				$('#s1y_div').hide();
				$('#s2y_div').hide();
				$('#s1role_div').hide();
				$('#s2role_div').hide();
				$('#ref_code_div').hide();
				$('#submit_button_div').hide();

			}
			else
			{
				$("#stage_div").show();

				/* Make sure form is still submitable if user changes
				   the journal after completing the rest of the form */

				switch($('#stage').val()) {

					// Stage 1

					case '1':

						if ($('#reviewer_role').val() == "1") {

							$('#stage1_div').show();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').show();
							$('#stage2_role2_orphans_div').hide();
						}

						if ($('#reviewer_role').val() == "2") {

							$('#stage1_div').hide();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').show();
						}

						$('#stage2_div').hide();

						if ($('#reviewer_role').val() == "1" && !$("input[name='stage1']:checked").val()) {

							$('#s1y_div').hide();
							$('#s1role_div').hide();
							$('#ref_code_div').hide();
							$('#submit_button_div').hide();
						}

						else

						{
							$('#s1y_div').show();
							$('#RRform input[name=stage1]').trigger('change');
						}

					break;

					/* Stage 2 */

					case '2':

						if ($('#reviewer_role').val() == "1") {

							$('#stage2_div').show();
							$('#stage1_role1_orphans_div').show();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();
						}

						if ($('#reviewer_role').val() == "2") {

							$('#stage2_div').hide();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').show();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();
						}

						$('#stage1_div').hide();

						if ($('#reviewer_role').val() == "1" && !$("input[name='stage2']:checked").val()) {

							$('#s2y_div').hide();
							$('#s2role_div').hide();
							$('#ref_code_div').hide();
							$('#submit_button_div').hide();
						}

						else

						{							
							$('#RRform input[name=stage2]').trigger('change');
						}

					break;

					/* Stage 1 and 2 */

					case '3':

						if ($('#reviewer_role').val() == "1") {

							$('#stage1_div').hide();
							$('#stage2_div').hide();

							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();

							$('#stageboth_div').show();

							if (!$("input[name='stage1_both']:checked").val() || !$("input[name='stage2_both']:checked").val()) {

								$('#s1y_div').hide();
								$('#s1role_div').hide();

								$('#s2y_div').hide();
								$('#s2role_div').hide();

								$('#ref_code_div').hide();
								$('#submit_button_div').hide();
							}

							else

							{
								$('#RRform input[name=stage1]').trigger('change');
							}
						}

						else

						{
							$('#stage1_div').hide();
							$('#stage2_div').hide();

							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();

							$('#stageboth_div').hide();
							$('#ref_code_div').show();
						}

					break;
				}
			}
		});


		$("#stage").change(function() { 
	
			if ( $(this).val() == "0") {

				$('#stage1_div').hide();
				$('#stage2_div').hide();

				$('#stage1_role1_orphans_div').hide();
				$('#stage1_role2_orphans_div').hide();
				$('#stage2_role1_orphans_div').hide();
				$('#stage2_role2_orphans_div').hide();

				$('#s1y_div').hide();
				$('#s2y_div').hide();			

				$('#stageboth_div').hide();
				$('#doi_div').hide();

				$('#s1role_div').hide();
				$('#s2role_div').hide();	

				$('#ref_code_div').hide();
				$('#submit_button_div').hide();				
				
			}
			else
			{
				/* Only show sub-stages if the user wants to give AUTHOR feedback */

				$('#s1y_div').hide();
				$('#s2y_div').hide();

				// Author

				if ($('#reviewer_role').val() == 1) {

					$('#submit_button_div').hide();

					switch($('#stage').val()) {

						/* Stage 1 (author) */

						case '1':

							$('#stage1_div').show();
							$('#stage2_div').hide();
							$('#stageboth_div').hide();
							$('#doi_div').hide();
							$('#s2role_div').hide();

							if (!$("input[name='stage1']:checked").val()) {

								$('#ref_code_div').hide();
								$('#s1y_div').hide();
								$('#s2y_div').hide();
								$('#s1role_div').hide();
								$('#s2role_div').hide();
							}

							else

							{
								$('#s1y_div').show();

								$('#RRform input[name=s1year_choice]').trigger('change');
							}

						break;

						/* Stage 2 (author) */

						case '2':

							$('#stage1_div').hide();
							$('#stage2_div').show();
							$('#stageboth_div').hide();
							$('#s1role_div').hide();

							if (!$("input[name='stage2']:checked").val()) {

								$('#ref_code_div').hide();
								$('#s1y_div').hide();
								$('#s2y_div').hide();
								$('#s1role_div').hide();
								$('#s2role_div').hide();
							}

							else

							{
								$('#s2y_div').show();

								$('#RRform input[name=s2year_choice]').trigger('change');
							}

							if ($("input[name='stage2']:checked").val() == "7") {

								$('#doi_div').show();
							}

							else

							{
								$('#doi_div').hide();
							}

						break;

						/* Stage 1 and 2 (author) */

						case '3':

							$('#stage1_div').hide();
							$('#stage2_div').hide();
							$('#stageboth_div').show();

							$('#ref_code_div').hide();
							$('#s1y_div').hide();
							$('#s2y_div').hide();
							$('#s1role_div').hide();
							$('#s2role_div').hide();
							$('#doi_div').hide();

							if ($("input:radio[name='s1year_choice']").is(":checked")) {

	    							$('#s1y_div').show();
	    							$('#s2y_div').show();
	    						}

							$('#RRform input[name=stage1_both]').trigger('change');							
						break;
					}
				}
				
				/* Reviewer selected */

				else

				{
					/* Hide Stage 1 & 2 sub-stage sections */

					$('#stage1_div').hide();
					$('#stage2_div').hide();

					$('#s1y_div').hide();
					$('#s2y_div').hide();

					$('#s1role_div').hide();
					$('#s2role_div').hide();

					$('#ref_code_div').hide();
					$('#submit_button_div').hide();

					switch($('#stage').val()) {

						/* Stage 1 */

						case '1':

							$('#s1y_div').show();

							$('#RRform input[name=s1year_choice]').trigger('change');

						break;

						/* Stage 2 */

						case '2':

							$('#s2y_div').show();

							$('#RRform input[name=s2year_choice]').trigger('change');

						break;

						/* Stage 1 and 2 */

						case '3':

							if ($("input:radio[name='s1year_choice']").is(":checked")) {

								$('#s1y_div').show();
								$('#s2y_div').show();

								$('#RRform input[name=s1year_choice]').trigger('change');
							}

							else

							{
								$('#ref_code_div').hide();
								$('#s1y_div').show();
							}

						break;
					}
				}

				/*  For either role selection

					Show the corresponding orphan review DIVs for the selected stage/role */

				switch($('#stage').val()) {

					/* Stage 1 */

					case '1':
						
						/* Author */

						if ($('#reviewer_role').val() == "1") {

							$('#stage1_div').show();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').show();
							$('#stage2_role2_orphans_div').hide();
						}

						/* Reviewer */

						if ($('#reviewer_role').val() == "2") {

							$('#stage1_div').hide();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').show();
						}

						$('#stage2_div').hide();

					break;


					/* Stage 2 */

					case '2':
						
						/* Author */

						if ($('#reviewer_role').val() == "1") {

							$('#stage2_div').show();
							$('#stage1_role1_orphans_div').show();
							$('#stage1_role2_orphans_div').hide();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();
						}


						/* Reviewer */

						if ($('#reviewer_role').val() == "2") {

							$('#stage2_div').hide();
							$('#stage1_role1_orphans_div').hide();
							$('#stage1_role2_orphans_div').show();
							$('#stage2_role1_orphans_div').hide();
							$('#stage2_role2_orphans_div').hide();
						}

						$('#stage1_div').hide();

					break;


					/* Stage 1 and 2 */

					case '3':
					
						$('#stage1_role1_orphans_div').hide();
						$('#stage1_role2_orphans_div').hide();
						$('#stage2_role1_orphans_div').hide();
						$('#stage2_role2_orphans_div').hide();

					break;
				}
			}

			$('html, body').animate({scrollTop:$(document).height()}, '250');
		});


		/* Check the user has selected a Stage 1 sub stage */

		$('#RRform input[name=stage1]').on('change', function() {

			if ( $(this).is(":checked")) {

				$('#s1y_div').show();
				$('#RRform input[name=s1year_choice]').trigger('change');

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}
		});


		/* Check the user has selected a Stage 2 sub stage */

		$('#RRform input[name=stage2]').on('change', function() {

			if ( $(this).is(":checked")) {

				$('#s2y_div').show();
				$('#RRform input[name=s2year_choice]').trigger('change');

				if ($(this).val() == "7") {

					$('#doi_div').show();
				}

				else

				{
					$('#doi_div').hide();
				}

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}
		});

		
		/* Check the user has selected a Stage 1 AND Stage 2 sub stage */

		$('#RRform input[name=stage1_both]').on('change', function() {

			if ($("input:radio[name='stage2_both']").is(":checked")) {

				$('#s1y_div').show();
				$('#RRform input[name=s1year_choice]').trigger('change');
			}
		});

		$('#RRform input[name=stage2_both]').on('change', function() {

			if ($("input[name='stage2_both']:checked").val() == "7") {

				$('#doi_div').show();
			}
			
			else

			{
				$('#doi_div').hide();
			}

			if ($("input:radio[name='stage1_both']").is(":checked")) {

				$('#s1y_div').show();
				$('#RRform input[name=s1year_choice]').trigger('change');

				if ($("input:radio[name='s1year_choice']").is(":checked")) {

					$('#s2y_div').show();
					$('#RRform input[name=s2year_choice]').trigger('change');
				}

				if ($("input:radio[name='s1year_choice']").is(":checked") && $("input:radio[name='s2year_choice']").is(":checked") ) {

					$('#ref_code_div').show();

					if ( $("#ref_code").val().length >= minLength && $("#ref_code").val().length <= maxLength ) {

	   	 				$('#submit_button_div').show();
	   	 			}	   	 			
				}

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}

		});


		/* Check if stage 1 year data has been entered */

		$('#RRform input[name=s1year_choice]').on('click change', function() {

			/* For single year and year range, check valid inputs */

			$year1 = false;

			switch($("input[name='s1year_choice']:checked").val()) {

				case '1':

					if ($('#s1singleyear').val() != '0') {

						$year1 = true;
					}

				break;


				case '2':

					if ($('#s1startyear').val() != '0' && $('#s1endyear').val() != '0') {

						if ($('#s1startyear').val() < $('#s1endyear').val()) {

							$year1 = true;
						}
					}

				break;


				case '3':

					$year1 = true;

				break;


				case '4':

					$year1 = true;

				break;
			}

			/* if a valid selection has been made, display next element(s) */

			if ($year1 == true) {

				/* If both stages have been chosen, display Stage 2 year range DIV */

				if ($('#stage').val() == "3") {

					$('#s2y_div').show();
					$('#RRform input[name=s2year_choice]').trigger('change');
				}

				/* Otherwise display ref code */

				else

				{
					$('#s1role_div').show();
					$('#RRform input[name=s1_academic_radio]').trigger('click');
					$("#s1_academic_role").trigger('change');
				}

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}

			else

			{
				$('#s2y_div').hide();
				$('#s1role_div').hide();
				$('#s2role_div').hide();
				$('#ref_code_div').hide();
				$('#submit_button_div').hide();
			}
		});


		/* Check if stage 2 year data has been entered */

		$('#RRform input[name=s2year_choice]').on('click change', function() {

			/* For single year and year range, check valid inputs */

			$year2 = false;

			switch($("input[name='s2year_choice']:checked").val()) {

				case '1':

					if ($('#s2singleyear').val() != '0') {

						$year2 = true;
					}

				break;


				case '2':

					if ($('#s2startyear').val() != '0' && $('#s2endyear').val() != '0') {

						if ($('#s2startyear').val() < $('#s2endyear').val()) {

							$year2 = true;
						}
					}

				break;


				case '3':

					$year2 = true;

				break;


				case '4':

					$year2 = true;

				break;
			}

			/* If a valid selection has been made, display next element(s) */

			if ($year2 == true) {

				if ($('#stage').val() == "3") {

					$('#s1role_div').show();
					$('#RRform input[name=s1_academic_radio]').trigger('click');
					$("#s1_academic_role").trigger('change');
				}

				else

				{
					$('#s2role_div').show();
					$('#RRform input[name=s2_academic_radio]').trigger('click');
					$("#s2_academic_role").trigger('change');

				}

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}

			else

			{
				$('#s1role_div').hide();
				$('#s2role_div').hide();
				$('#ref_code_div').hide();
				$('#submit_button_div').hide();
			}
		});


		/* Stage 1 academic role */

		$('#RRform input[name=s1_academic_radio]').on('click', function() {

			if ( $(this).val() == "1") {

				if ($("#s1_academic_role").val() == "0") {

					$('#s2role_div').hide();
					$('#ref_code_div').hide();
		       		$('#submit_button_div').hide();
				}

				else if ($("#s1_academic_role").val() == "16") {

					$('#s1_academic_role_text').focus();
				}
			}

			else

			{
				if ($('#stage').val() == "3") {

					$('#s2role_div').show();
					$("#s2_academic_role").trigger('change');
				}

				else

				{
					$("#s1_academic_role_text_span").text("");
			        $('#ref_code_div').show();
			        $("#ref_code").trigger('change');
			        $('html, body').animate({scrollTop:$(document).height()}, '500');
		    	}
			}
		});


		$("#s1_academic_role").change(function() {

		    if ( $(this).val() == "16") {

		        $('#s1_academic_role_text').focus();
		        $('#s2role_div').hide();
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();

		    }

		    else if ( $(this).val() == "0") {

		    	$('#s2role_div').hide();
		    	$('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		        $('#s1_academic_role_text').val('');
		        $("#s1_academic_role_text_span").text("");

		    }

		    else

		    {
				if ($('#stage').val() == "3") {

					$('#s2role_div').show();
					$("#s2_academic_role").trigger('change');
					$('html, body').animate({scrollTop:$(document).height()}, '500');
				}

				else

				{
					$('#s1_academic_role_text').val('');
					$("#s1_academic_role_text_span").text("");
			        $('#ref_code_div').show();
			        $("#ref_code").trigger('change');
			        $('html, body').animate({scrollTop:$(document).height()}, '500');
		    	}
		    }
		});


		$("#s1_academic_role_text").on("keydown keyup change", function(){

		    var value = $(this).val();

		    if (value.length < 3) {
		    	
		        $("#s1_academic_role_text_span").text("Text is too short");
		        $('#s2role_div').hide();
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		    }

		    else if (value.length > 255) {

		        $("#s1_academic_role_text_span").text("Text is too long");
		        $('#s2role_div').hide();
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		    }

		    else

		    {
		        $("#s1_academic_role_text_span").text("");
		        
				if ($('#stage').val() == "3") {

					$('#s2role_div').show();
				}

				else

				{
			        $('#ref_code_div').show();
			        $("#ref_code").trigger('change');			        
		    	}

		    	$('html, body').animate({scrollTop:$(document).height()}, '500');
		    }
		});


		/* Stage 2 academic role */

		$('#RRform input[name=s2_academic_radio]').on('click', function() {

			if ( $(this).val() == "1") {

				if ($("#s2_academic_role").val() == "0") {

					$('#ref_code_div').hide();
		       		$('#submit_button_div').hide();
				}

				else if ($("#s2_academic_role").val() == "16") {

					$('#s2_academic_role_text').focus();
				}
			}

			else

			{
				$("#s2_academic_role_text_span").text("");
		        $('#ref_code_div').show();
		        $("#ref_code").trigger('change');	
		        $('html, body').animate({scrollTop:$(document).height()}, '500');	    	
			}
		});


		$("#s2_academic_role").change(function() {

		    if ( $(this).val() == "16") {

		        $('#s2_academic_role_text').focus();
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		    }

		    else if ( $(this).val() == "0") {

		    	$('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		        $('#s2_academic_role_text').val('');
		        $("#s2_academic_role_text_span").text("");
		    }

		    else

		    {
				$('#s2_academic_role_text').val('');
				$("#s2_academic_role_text_span").text("");
		        $('#ref_code_div').show();
		        $("#ref_code").trigger('change');
		        $('html, body').animate({scrollTop:$(document).height()}, '500');
		    }
		});


		$("#s2_academic_role_text").on("keydown keyup change", function(){

		    var value = $(this).val();

		    if (value.length < 3) {
		    	
		        $("#s2_academic_role_text_span").text("Text is too short");
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		    }

		    else if (value.length > 255) {

		        $("#s2_academic_role_text_span").text("Text is long");
		        $('#ref_code_div').hide();
		        $('#submit_button_div').hide();
		    }

		    else

		    {
		        $("#s2_academic_role_text_span").text("");
		        $('#ref_code_div').show();
		        $("#ref_code").trigger('change');
		    	$('html, body').animate({scrollTop:$(document).height()}, '500');
		    }
		});


		/* Reference code check */

		$("#ref_code").on("keydown keyup change", function(){

		    if($("#ref_code_div").is(":visible")) {

			    var value = $(this).val();

			    if (value.length < minLength) {
			    	
			        $("#ref_code_span").text("Text is too short");
			    	$('#submit_button_div').hide();
			    }

			    else if (value.length > maxLength) {

			        $("#ref_code_span").text("Text is too long");
			    	$('#submit_button_div').hide();
			    }

			    else

			    {
			        $("#ref_code_span").text("");
			    	$('#submit_button_div').show();
			    }
			}
		});


	    function lookupDOI(doi) {

	        if ($('#doi').val() == '') {

	            alert('Please enter a DOI before searching');
	        }

	        else

	        {

	            $.getJSON("doi_lookup.php", {doi: doi}, function (response) {

	                switch(response.available) {
	                    
	                    case 'false':

	                        $('#doi_lookup').html('');

	                        alert('No article found with this DOI - please check you have entered the correct DOI.\n\nIf this article has very recently been published, Crossref may not yet have it listed.\n\nIf you are certain this is the correct DOI, please leave it as entered, despite this message.');

	                    break;

	                    
	                    case 'true':

	                        $('#doi_lookup').html('<br /><br /><b>Is this the article?</b>&nbsp;<a href="#" title="Confirm" onClick="confirmDOIlookup();">&#9989;</a>&nbsp;<a href="#" title="Clear" onClick="clearDOIlookup();">&#10060;</a><br /><br />' + response.reference);
	                        $('#doi_search').attr('ref', response.reference);

	                    break;
	                }

	            });
	        }
	    }


	    function clearDOIlookup() {

	        $('#doi_lookup').html('');
	        $('#doi').val('');
	        $('#doi_search').attr('ref', '');
	    }


	    function confirmDOIlookup() {

	        $('#doi_lookup').html('<br /><br />' + $('#doi_search').attr('ref'));
	        $('#doi_search').attr('ref', '');

	        if ($('#stage').val() == "3") {

				$('#s1y_div').show();

				$('html, body').animate({scrollTop:$(document).height()}, '500');
			}

			/* Otherwise display ref code */

			else

			{

	            $('#s2y_div').show();

	            $('html, body').animate({scrollTop:$(document).height()}, '500');
	    	}
	    }


		function scrollToBottomOfDiv (id) {

			setTimeout(function () {

		        divOffset = $('#' + id).offset().top;
		        divHeight = $('#' + id).height();
		        viewportHeight = $(window).height();

		        $('body, html').animate({scrollTop : divOffset + divHeight - viewportHeight});

		    }, 250);
		};

		/* routine to insert existing sub-stage and year data from existing reviews, if user has chosen an orphan piece of feedback, where an existing review exists from another user */

		function set_existing_orphan_details (orphanstage, orphansubstage, orphanstartyear, orphanendyear, journal) {

			$jselect2 = $("#journal").select2();
			$jselect2.val(journal).trigger("change");

			$("#journal option[value!=" + journal + "]").prop("disabled", true);

			$("input[name=stage" + orphanstage + "][value='" + orphansubstage + "']").prop("checked", true);
			$("input[name=stage" + orphanstage + "][value!='" + orphansubstage + "']").prop("disabled", true);

			$('#s' + orphanstage + 'y_div').show();

			if( (orphanstartyear != "777" && orphanendyear != "777") && (orphanstartyear != "999" && orphanendyear != "999") ) {

				if (orphanstartyear == orphanendyear) {

					/* select 'Single year' radio and enable it */

					$("input[name='s" + orphanstage + "year_choice'][value='1']").prop("checked", true);
					$("input[name='s" + orphanstage + "year_choice'][value='1']").prop("disabled", false);

					/* disable other radio choices */

					$("input[name='s" + orphanstage + "year_choice'][value!='1']").prop("disabled", true);

					/* enable single year dropdown */

					$("#s" + orphanstage + "singleyear").prop("disabled", false);

					/* set 'Single year' dropdown to correct year */

					$("#s" + orphanstage + "singleyear").val(orphanstartyear);

					/* disable all other single year choices */

					$("#s" + orphanstage + "singleyear option[value!=" + orphanstartyear + "]").prop("disabled", true);

					/* clear any 'Range of year' dropdown choices (from previous choices) */

					$("#s" + orphanstage + "startyear").val('0');
					$("#s" + orphanstage + "endyear").val('0');

					$("#s" + orphanstage + "startyear").prop("disabled", true);
					$("#s" + orphanstage + "endyear").prop("disabled", true);
				}

				else

				{
					/* select 'Range of years' radio and enable it */

					$("input[name='s" + orphanstage + "year_choice'][value='2']").prop("checked", true);
					$("input[name='s" + orphanstage + "year_choice'][value='2']").prop("disabled", false);

					/* disable other radio choices */

					$("input[name='s" + orphanstage + "year_choice'][value!='2']").prop("disabled", true);

					/* enable year range dropdowns */

					$("#s" + orphanstage + "startyear").prop("disabled", false);
					$("#s" + orphanstage + "endyear").prop("disabled", false);


					/* set 'Range of years' dropdowns to correct years */

					$("#s" + orphanstage + "startyear").val(orphanstartyear);
					$("#s" + orphanstage + "endyear").val(orphanendyear);

					/* enable the selected start year and disable all other start year choices */

					$("#s" + orphanstage + "startyear option[value!=" + orphanstartyear + "]").prop("disabled", true);

					/* enable the selected end year and disable all other start year choices */

					$("#s" + orphanstage + "endyear option[value!=" + orphanendyear + "]").prop("disabled", true);

					/* reset and disable the 'Single year' dropdown */

					$("#s" + orphanstage + "singleyear").prop('disabled', true);

					$("#s" + orphanstage + "singleyear").val('0');
				}

				/* if year information has been specified as either a single or range of years, show next form item */

				$('#s' + orphanstage + 'role_div').show();
			}

			/* In this case (start/end year = 777 or 999) a previous user answered the year information for this stage with either:

			'Prefer not to answer'

			or

			'Don't know / don't recall'

			so we leave this section open to data being entered (we may get missing year information), and thus must clear all options */

			else

			{
				/* hide academic role form element */

				$('#s' + orphanstage + 'role_div').hide();

				clearYear(orphanstage);
			}

			alert('We have preselected certain responses (e.g. journal, manuscript outcome) for the feedback you are about to give.\n\nThis is because these responses have already been provided for this stage of the manuscript by another user (most likely the person who invited you).\n\nFor reasons relating to data integrity, we do not allow potentially conflicting metadata from different people for feedback of the same manuscript.');
		}


		/* function to clear */

		function clearSelection (stage, role, clearRadio) {
			
			opposite_stage = (stage == 1 ? 2 : 1);

			$("#journal option[value!=-1]").prop("disabled", false);

			clearYear(opposite_stage);

			if (clearRadio == true) {

				$('input[type=radio][name=stage' + stage + '_role' + role + '_orphan_id]').prop('disabled', false);
				$('input[type=radio][name=stage' + stage + '_role' + role + '_orphan_id]').prop('checked', false);
			}

			$('input[type=radio][name=stage' + opposite_stage + ']').prop('disabled', false);
			$('input[type=radio][name=stage' + opposite_stage + ']').prop('checked', false);

			$('#orphan_selected').val('0');

			$('#ref_code').val('');
			$('#ref_code').prop({'readonly':false}, {'disabled': false});

			/* hide form elements to ensure the user fills them in after clearing any selections resulting from choosing (and then clearing) an orphan review */

			if ($('#reviewer_role').val() == "1") {

				$('#s' + opposite_stage + 'y_div').hide();
				$('#s' + opposite_stage + 'role_div').hide();
				$('#doi_div').hide();
				$('#ref_code_div').hide();
				$('#submit_button_div').hide();
			}

			else if ($('#reviewer_role').val() == "2") {

				$('#s' + opposite_stage + 'role_div').hide();
				$('#doi_div').hide();
				$('#ref_code_div').hide();
				$('#submit_button_div').hide();
			}

			/* $('#ref_code').trigger('change'); */

		}


		function clearYear (stage) {

			/* clear/reset all year X form elements */

			$("input[name='s" + stage + "year_choice'][value!=-1]").prop("checked", false);
			$("input[name='s" + stage + "year_choice'][value!=-1]").prop("disabled", false);

			$("#s" + stage + "singleyear option[value!=-1]").prop("disabled", false);
			$("#s" + stage + "singleyear").prop("disabled", false);
			$("#s" + stage + "singleyear").val('0');

			$("#s" + stage + "startyear").prop("disabled", false);
			$("#s" + stage + "endyear").prop("disabled", false);

			$("#s" + stage + "startyear option[value!=-1]").prop("disabled", false);
			$("#s" + stage + "endyear option[value!=-1]").prop("disabled", false);

			$("#s" + stage + "startyear").val('0');
			$("#s" + stage + "endyear").val('0');
		}

		/* change the journal dropdown to the value specified */

		function switchJournal (journal) {

			/* Only change the journal if it's not already set to the target journal */

			if ($("#journal").val() != journal) {

				/* https://jeesite.gitee.io/front/jquery-select2/4.0/index.htm#programmatic */

				$jselect2 = $("#journal").select2();

				$jselect2.val(journal).trigger("change");

				alert('We have changed the journal in Question 2 to match the journal of this unlinked feedback.\n\nThis is because the vast majority of Registered Reports manuscripts stay at the same journal for both Stage 1 and 2 of peer review.\n\nIf this was not the case for the manuscript you are giving feedback on, you may select a different journal for Question 2.');
			}

			$('#ref_code_div').hide();
			$('#submit_button_div').hide();
		}

		/* https://api.jquery.com/submit/ */

		$( "#RRform" ).submit(function( event ) {
			
			/* check if user has entered a ref code that is amongst the ref codes of their orphan reviews */

			/* this only applies is the user has chosen Stage 1 or 2, but not both stages */

			if ($('#stage').val() == 1 || $('#stage').val() == 2) {

				var submitstatus = true;

				switch($('#reviewer_role').val()) {

					/* author */

					case '1':

						switch($('#stage').val()) {

							case '1':

								if ($('#RRform input[name=stage2_role1_orphan_id]').is(":checked") == false) {

									if(refcodes.indexOf($("#ref_code").val()) != -1) {

										submitstatus = false;		
									}
								}

							break;

							
							case '2':

								if ($('#RRform input[name=stage1_role1_orphan_id]').is(":checked") == false) {

									if(refcodes.indexOf($("#ref_code").val()) != -1) {

										submitstatus = false;		
									}
								}

							break;
						}

					break;

					/* reviewer */

					case '2':

						switch($('#stage').val()) {

							case '1':

								if ($('#RRform input[name=stage2_role2_orphan_id]').is(":checked") == false) {

									if(refcodes.indexOf($("#ref_code").val()) != -1) {

										submitstatus = false;		
									}
								}

							break;

							
							case '2':

								if ($('#RRform input[name=stage1_role2_orphan_id]').is(":checked") == false) {

									if(refcodes.indexOf($("#ref_code").val()) != -1) {

										submitstatus = false;		
									}
								}

							break;
						}

					break;

				}

				if (submitstatus == true) {

					return;
				}
				
				else

				{
					alert('The reference code you have chosen already exists for an unlinked piece of feedback you have given.\n\nPlease choose a unique reference code.');
					event.preventDefault();
				}
			}			
		});