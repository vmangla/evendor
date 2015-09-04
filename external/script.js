$(document).ready(function() {
//change CAPTCHA on each click or on refreshing page
    $("#reload").click(function() {
        $("#img").remove();
        $('<img id="img" src="http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/external/captcha.php" />').appendTo("#imgdiv");
    });

//validation function
    $('#button').click(function() {
        var name = $("#username1").val();
        var email = $("#email1").val();
        var captcha = $("#captcha1").val();

        if (name == '' || email == '' || captcha == '')
        {
            alert("Fill All Fields");
        }

        else
        {	//validating CAPTCHA with user input text
            var dataString = 'captcha=' + captcha;
            $.ajax({
                type: "POST",
                url: "verify.php",
                data: dataString,
                success: function(html) {
                    alert(html);
                }
            });
        }
    });
});