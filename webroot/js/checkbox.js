function onSubmit() 
{ 
    var fields = $("input[name='genres[]']").serializeArray(); 
    if (fields.length == 0) 
    { 
        document.getElementById("invalid").style.display = 'inline';
        // cancel submit
        return false;
    }
}

// register event on form, not submit button
$('#update').submit(onSubmit)
$('#add').submit(onSubmit)
