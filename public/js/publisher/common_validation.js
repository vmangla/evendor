/**
 * Validation with FORM or Without FORM
 * Developed By Talib Aziz
 */

function common_validation(pContainer) 
{   
	var isError=0;
	
	if(pContainer!=null)
	{
		var vContainer=pContainer;//$(pContainer).attr("id");

		//$('#'+vContainer).find(".inline-error").remove();
		$('body').find(".inline-error").remove();
		$('body').find(".div-error").remove();
		$('body').find(".div-success").remove();
		$('body').find(".div-notice").remove();
	
		$('#'+vContainer+' input:text.req').each(function(i,v)
		{	
			if($.trim($(this).val())=="")
			{
				isError=1;
				//alert($(this).attr("name"));
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}   		
 		});
		
		$('#'+vContainer+' textarea.req').each(function(i,v)
		{	
			if($.trim($(this).val())=="")
			{
				isError=1;
				//alert($(this).attr("name"));
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}   		
 		});
		
		$('#'+vContainer+' input:password.req').each(function(i,v)
		{	
			if($.trim($(this).val())=="")
			{
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}   		
		 });
		$('#'+vContainer+' select.req').each(function(i,v) 
		{	
			if($.trim($(this).val())=="" || $(this).val()=="select")
			{
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}   		
 		});
		
		$('#'+vContainer+' input:text.special').each(function(i,v)
		{	
			if($.trim($(this).val())!="")
			{
				if(isSpclChar($(this).val()))
				{ 	
					isError=1;				
					$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
				}
			}	
		});
		
		$('#'+vContainer+' span.req').each(function(i, v) 
		{
			if($(this).find('input:radio:checked,input:checkbox:checked').length<=0)
			{		
				isError=1;
				$(this).append("<div class='inline-error'>"+$(this).attr("message")+"</div>")
			}
		});
		
		$('#'+vContainer+' input:text.validmail').each(function(i, v) 
		{
			email = $(this).val();
			if(email!="")
			{					
				if(!isValidEmailAddress(email))
				{ 
					isError=1;
					$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>")
				}
			}	 				
		});
		
		$('#'+vContainer+' input:text.confemail').each(function(i, v) 
		{
			email = $(this).val();
			if(email!="")
			{					
				if(!isValidEmailAddress(email))
				{ 
					isError=1;
					$(this).parent().find(".inline-error").remove();
					$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>")
				}
			}				
		});
		
		/*//would be same as confpass class as given below
		if($('#'+vContainer).hasClass('validmail') && $('#'+vContainer).hasClass('confemail'))
		{
			if(($('#'+vContainer+' input:text.validmail').val()!="" && $('#'+vContainer+' input:text.confemail').val()!="") && ($('#'+vContainer+' input:text.validmail').val()!=$('#'+vContainer+' input:text.confemail').val()))
			{
				isError=1;
				$('#'+vContainer+' input:text.confemail').parent().find(".inline-error").remove();
				$('#'+vContainer+' input:text.confemail').parent().append("<div class='inline-error'>"+$('#'+vContainer+' input:text.confemail').attr("invalidmessage")+"</div>");
			}
		}*/
		
		$('#'+vContainer+' input:password.confpass').each(function(i, v) 
		{
			if($.trim($(this).val())!="")
			{
				if($('#'+vContainer+' input:password.pass') && $('#'+vContainer+' input:password.pass').val()!=$(this).val())
				{
					isError=1;				
					$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
				}
				
			}	
		});
		
		$('#'+vContainer+' input:text.phone').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && !validatePhone($(this).val()))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		
		$('#'+vContainer+' input:text.isbn').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && !validateIsbn($(this).val()))
			 { 
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		
		$('#'+vContainer+' input:text.number').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && isNaN($(this).val()))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		
		$('#'+vContainer+' input:text.digit').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && (isNaN($(this).val()) || $(this).val().length!=$(this).attr("maxlength")))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		
		$('#'+vContainer+' input:text.zip').each(function(i,v)
		{	
			if($.trim($(this).val())!="" && !validateZIP($(this).val()))
			{ 
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			}
		});
		
		$('#'+vContainer+' input:checkbox.req').each(function(i,v) 
		{
			if(!$(this).attr('checked'))
			{ 
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}
		});
		
		$('#'+vContainer+' input:radio.req').each(function(i,v) 
		{
			if(!$(this).attr('checked'))
			{ 
				isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("message")+"</div>");
			}
		});
		
		$('#'+vContainer+' input:text.date').each(function(i,v) 
		{
			myDate = $(this).val();
			if(myDate!="")
			{					
				if(!isDate(myDate))
				{ 
					isError=1;
					$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
				}
			}	
			
		});
					
		$('#'+vContainer+' input:text.datemmyyyy').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && !isMMYYYY($(this).val()))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		
		$('#'+vContainer+' input:text.interphone').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && !validateInternationalPhone($(this).val()))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		$('#'+vContainer+' input:text.maxval').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && (isNaN($(this).val()) || parseInt($(this).val()) > parseInt($(this).attr("maxval"))))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
		$('#'+vContainer+' input:text.maxdigit').each(function(i,v)
		{	
			 if($.trim($(this).val())!="" && (isNaN($(this).val()) || $(this).val().length > $(this).attr("maxdigit")))
			 { 
				 isError=1;
				$(this).parent().append("<div class='inline-error'>"+$(this).attr("invalidmessage")+"</div>");
			 }			
		});
	}
	
	if(isError==1)
	{
		//$('#'+vContainer).find(".inline-error").filter(function()
		$('#'+vContainer).find(".inline-error:first").filter(function()
		{
			//alert($(this).html());
		     //$(this).prev().val("tajlssjlkdjf");
			//if($(this).prev().attr('type')=='text')
			//{
				$(this).prev().focus();
			//}
		});
		return false;
	}
	else
	{
		return true;
	}
}
  
  
function isValidEmailAddress(emailAddress) 
{
	var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}
function validateZIP(field) 
{
	//alert(field.length);
	var valid = "0123456789-";
	var hyphencount = 0;
 
	for (var i=0; i <field.length; i++) 
	{
		temp = "" + field.substring(i, i+1);
		if (temp == "-") hyphencount++;
		if (valid.indexOf(temp)=="-1") 
		{
			//alert("Invalid characters in your zip code.  Please try again.");
			return false;
		}
	}
		return true;
}  
  
function isSpclChar(field)
{
	//alert(field);
	if ( /[^A-Za-z\d]/.test(field)) 
	{
        //alert("Please enter only letter and numeric characters");
        return true;
    }
	else
	{
		return false;
	}
}
function validatePhone(field)
{
	var regexObj = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
	
	if(regexObj.test(field)) 
	{
		//alert("Valid phone number");
		//var formattedPhoneNumber=subjectString.replace(regexObj, "($1) $2-$3");
		return true;
	} 
	else 
	{
		//alert("Invalid phone number");
		return false;
	}
}

function validateIsbn(field)
{
	
	//var regexObj = /((978[\� ])?[0-9][0-9\� ]{10}[\� ][0-9xX])|((978)?[0-9]{9}[0-9Xx])/;
	//var regexObj = /((978[\--� ])?[0-9][0-9\--� ]{10}[\--� ][0-9xX])|((978)?[0-9]{9}[0-9Xx])/;
	var regexObj = /^([0-9]|[a-z])+([0-9a-z]+)$/i;
	
	if(regexObj.test(field)) 
	{
		//alert("Valid ISBN number");
		return true;
	}
	else 
	{
		//alert("Invalid ISBN number");
		return false;
	}
}


function isMMYYYY(txtMonthYear) 
{
    var objDate,  // date object initialized from the txtDate string
        mSeconds, // txtDate in milliseconds
        day,      // day
        month,    // month
        year;     // year
    // date length should be 10 characters (no more no less)
    if (txtMonthYear.length !== 7) 
	{
        return false;
    }
    // third and sixth character should be '/'
    if (txtMonthYear.substring(2,3) !== '/') 
	{
        return false;
    }
    // extract month, day and year from the txtDate (expected format is mm/yyyy)
    // subtraction will cast variables to integer implicitly (needed
    // for !== comparing)
    month = txtMonthYear.substring(0, 2) - 1; // because months in JS start from 0
	day =01;
    year = txtMonthYear.substring(3, 7) - 0;
    // test year range
    if (year < 1000 || year > 3000) 
	{
        return false;
    }
    // convert txtDate to milliseconds
    mSeconds = (new Date(year, month, day)).getTime();
    // initialize Date() object from calculated milliseconds
    objDate = new Date();
    objDate.setTime(mSeconds);
    // compare input date and parts from Date() object
    // if difference exists then date isn't valid
    if (objDate.getFullYear() !== year || objDate.getMonth() !== month) 
	{
        return false;
    }
    // otherwise return true
    return true;
}
function isDate(txtDate) 
{
    var objDate,  // date object initialized from the txtDate string
        mSeconds, // txtDate in milliseconds
        day,      // day
        month,    // month
        year;     // year
    // date length should be 10 characters (no more no less)
    if (txtDate.length !== 10) {
        return false;
    }
    // third and sixth character should be '/'
    if (txtDate.substring(2, 3) !== '/' || txtDate.substring(5, 6) !== '/') {
        return false;
    }
    // extract month, day and year from the txtDate (expected format is mm/dd/yyyy)
    // subtraction will cast variables to integer implicitly (needed
    // for !== comparing)
    month = txtDate.substring(0, 2) - 1; // because months in JS start from 0
    day = txtDate.substring(3, 5) - 0;
    year = txtDate.substring(6, 10) - 0;
    // test year range
    if (year < 1000 || year > 3000) {
        return false;
    }
    // convert txtDate to milliseconds
    mSeconds = (new Date(year, month, day)).getTime();
    // initialize Date() object from calculated milliseconds
    objDate = new Date();
    objDate.setTime(mSeconds);
    // compare input date and parts from Date() object
    // if difference exists then date isn't valid
    if (objDate.getFullYear() !== year ||
        objDate.getMonth() !== month ||
        objDate.getDate() !== day) {
        return false;
    }
    // otherwise return true
    return true;
}
function validateInternationalPhone(pNumber)
{
	//var regexp = /^\+(?:[0-9]?){6,14}[0-9]$/;
	var regexp =/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;
	
    if(regexp.test(pNumber)) 
	{
       return true;
    } 
	else 
	{
      return false;
    }
} 