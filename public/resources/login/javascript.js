$('.message a').click(function ()
{
    $('form').animate({height:"toggle",opacity:"toggle"},"slow");

});
$(document).on('click','#toggle_password',function()
{
    var x =document.getElementById("password");
    var icon=this;
    if(x.type==="password")
    {
        x.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }else
    {
        x.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
})