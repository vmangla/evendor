<?php
$dataModules=explode(",","0,1,2,3,4,5,6,7");
?>

<script type="text/javascript">
function getDashboard(hide_tab)
{
	var hide_array=hide_tab.split('-');
	$('li#t'+hide_array[1]+' a').removeClass('current');
	$('#tab'+hide_array[1]).hide();
		
	result = $('#tab1').html();
	$('li#t1 a').addClass('current');
	$('#tab1').hide().html(result).fadeIn("slow");
	return false;
}	

function getSettingPanel()
{
	result="<ul class=\"shortcut-buttons-set\"><?php if(in_array("0", $dataModules)) {?><li><a class=\"shortcut-button\" href=\"<?php echo $this->url(array('module'=>'publisher','controller'=>'profile','action'=>'index'),'',true);?>\" title=\"My Profile\" onclick=\"return goToPage(this.href,'tab4');\"><span><img src=\"<?php echo $this->serverUrl().$this->baseUrl();?>/public/css/publisher/images/staffe.png\" alt=\"My Profile\"><br>My Profile</span></a></li><?php } ?><?php if(in_array("0", $dataModules)) {?><li><a class=\"shortcut-button\" href=\"<?php echo $this->url(array('module'=>'publisher','controller'=>'profile','action'=>'updateprofile'),'',true);?>\" title=\"Update Profile\" onclick=\"return goToPage(this.href,'tab4');\"><span><img src=\"<?php echo $this->serverUrl().$this->baseUrl();?>/public/css/publisher/images/update_profile.png\" alt=\"Update Profile\"><br>Update Profile</span></a></li><?php } ?><?php if(in_array("0", $dataModules)) {?><li><a class=\"shortcut-button\" href=\"<?php echo $this->url(array('module'=>'publisher','controller'=>'profile','action'=>'changepassword'),'',true);?>\" title=\"Password Setting\" onclick=\"return goToPage(this.href,'tab4');\"><span><img src=\"<?php echo $this->serverUrl().$this->baseUrl();?>/public/css/publisher/images/advancedsettings.png\" alt=\"Settings\" ><br>Settings</span></a></li><?php } ?></ul>";
	
	//result = $('#tab4').html();
	
	$('#tab4').hide().html(result).fadeIn("slow");
	return false;

}

function goToPage(ajax_url,result_tab,tab_ajax)
{
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax="+tab_ajax,
		success: function(result) {
			$('li#t1 a').removeClass('current');
			$('#tab1').hide();
			var b_array=result_tab.split('b');
			//alert(b_array[1]);
			$('li#t'+b_array[1]+' a').addClass('current');
			$('#'+result_tab).hide().html(result).fadeIn("slow");

		}
	});
	
	return false;
}



/******************************************************/

function validate_common_form(pFormId,ajax_url,result_tab)
{
	
	if(pFormId)
	{
		if(!common_validation(pFormId))
		{
			return false;
		}
	}
	
	var form_data = $('#'+pFormId).serialize();
	//alert(form_data);
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: form_data+"&tab_ajax="+result_tab,
		//data: "{ajax:jai}",
		//dataType: "json",
		//contentType: "application/json; charset=utf-8",
		success: function(result) {
			//alert(result);
			$('#'+result_tab).hide().html(result).fadeIn("slow");
		}
	});
	return false;

}

function validate_pricing_form(pFormId,ajax_url)
{
	if(pFormId)
	{
		if(!common_validation(pFormId))
		{
			return false;
		}
	}	
	
	    var form_data = $('#'+pFormId).serialize();
		//alert(form_data);
		
		var refreshrate =$("#refreshrate");
		
	    $.ajax(
		{
           type: "POST",
           url: ajax_url,  
		   async: false,
           data: form_data+"&tab_ajax=book",
           success: function(val) 
		   {
			   refreshrate.html(val);
           }
     });
	
	
  return false;
  
}



function inactive(ajax_url,result_tab,tab_ajax)
{
	//alert(ajax_url);
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax="+tab_ajax,
		//data: "{ajax:jai}",
		//dataType: "json",
		//contentType: "application/json; charset=utf-8",
		success: function(result) {
			//alert(result);
			$('#'+result_tab).html(result);
		}
	});
	
	return false;
}

function active(ajax_url,result_tab,tab_ajax)
{
	//alert(ajax_url);
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax="+tab_ajax,
		//data: "{ajax:jai}",
		//dataType: "json",
		//contentType: "application/json; charset=utf-8",
		success: function(result) {
			//alert(result);
			$('#'+result_tab).html(result);
		}
	});
	
	return false;
}

function deleteAction(ajax_url,result_tab,tab_ajax)
{
	//alert(ajax_url);
	
	if(confirm('Are you sure you want to delete this record?'))
	{
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax="+tab_ajax,
		//data: "{ajax:jai}",
		//dataType: "json",
		//contentType: "application/json; charset=utf-8",
		success: function(result) {
			//alert(result);
			$('#'+result_tab).html(result);
		}
	});
	return false;
	}
return false;	
}

function deleteepubfile(ajax_url)
{
    if(confirm('Are you sure want to delete this file?'))
	{
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax=issue",
		success: function(result) {
			$('#tab2').hide().html(result).fadeIn("slow");
		}
	});
	
	return false;
	}
	else
	{
	   return false;
	}	
}

function deleteprice(ajax_url,tab_ajax)
{

    if(confirm('Are you sure want to delete this price?'))
	{
    var priceoutput =$("#priceoutput");
	var refreshrate =$("#refreshrate");

	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "tab_ajax="+tab_ajax,
		success: function(result) {
			refreshrate.html(result);
		}
	});
	
	return false;
	}
	else
	{
	   return false;
	}	
}

/*function validate_user(pFormId)
{
	if(pFormId)
	{
		if(!common_validation(pFormId))
		{
			return false;
		}
	}	
    
	    var booktype   =document.getElementById("booktype").value;
		var author     =document.getElementById("author").value;
		var category   =document.getElementById("category").value;
		var title      =document.getElementById("title").value;
		var isbn_number=document.getElementById("isbn_number").value;
		var total_pages=document.getElementById("total_pages").value;
		var description=document.getElementById("description").value;
		var createbook ='Add Book';
		
      
	    var dataString = 'booktype='+ booktype + '&author=' + author + '&category=' + category+ '&title=' + title+ '&isbn_number=' + isbn_number + '&total_pages=' + total_pages+ '&description=' + description+ '&createbook=' + createbook;

        $.ajax(
		{
           type: "POST",
           url: "<?php echo $createURL; ?>",  
		   async: false,
           data: dataString,
           success: function(val) 
		   {
			   $('#tab2').hide().html(val).fadeIn("slow");
           }
     });
	
	
	return false;
}
*/

function get_title_brands(ajax_url,pub_type_id)
{
//alert(ajax_url+'=>'+pub_type_id);
	$.ajax({
	    type: "POST",
		url: ajax_url,
		async: false,
		data: "cat_id="+pub_type_id+"&tab_ajax=book",
		success: function(result) {
			//alert(result);
			var result_array=result.split('^*^*');
			$('#title_brand').html(result_array[0]);
			$('#author_label').show().html(result_array[2]);
			$('#author_dropdown').show().html(result_array[3]);
			$('#page_label').show().html(result_array[4]);
			$('#page_field').show().html(result_array[5]);
			$('#isbn_label').show().html(result_array[6]);
			$('#isbn_field').show().html(result_array[7]);
			
			
			/*if(result_array[1]=='show_author')
			{
				$('#author_label').show().html(result_array[2]);
				$('#author_dropdown').show().html(result_array[3]);
				$('#page_label').show().html(result_array[4]);
				$('#page_field').show().html(result_array[5]);
			}
			else
			{
				//$('#author_label').css('display','none');
				//$('#author_dropdown').css('display','none');
				$('#author_label').hide().html(result_array[2]);
				$('#author_dropdown').hide().html(result_array[3]);
				$('#page_label').hide().html(result_array[4]);
				$('#page_field').hide().html(result_array[5]);
			}*/
		}
	});
	
	return false;
}

</script>