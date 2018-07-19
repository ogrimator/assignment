function submitForm(){
    $("#success").hide();
    $("#alert").hide();
    if($("#title").val() && $("#body").val() && $("#email").val()){
        $("#alert").html("Sending");
        $("#alert").show();
        data = {
                "title": $("#title").val(),
                "body": $("#body").val(),
                "email": $("#email").val()
        };
        if($("#image").val()){
            data["image"] = $("#image").val();
        }
        $.post(
            window.location.href,
            data,
            function(data){
                $("#title").val("");
                $("#body").val("");
                $("#email").val("");
                $('#image').val("");
                $("#success").show();
            }
        );
        $("#alert").hide();
        return false;
    }
    $("#alert").show();
    return true;
}
