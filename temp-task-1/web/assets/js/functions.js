$(document).ready(function()
{
    if ($("#categoriesTable"))
    {
        $('#categoriesTable').DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": CI_ROOT+"/categoriesTable",
            "sPaginationType": "full_numbers",
            "bSort": false
        });
    }
});

function recoverPassword(event)
{
	event.preventDefault();

	$("#div_recover_box").removeClass("hide-class");
	$("#div_login_box").addClass("hide-class");
}

function backLogin(event)
{
	event.preventDefault();

	$("#div_recover_box").addClass("hide-class");
	$("#div_login_box").removeClass("hide-class");
}

function login(event)
{
	event.preventDefault();

	var form = $("#loginForm");

    form.validate({
        debug: true,
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true
            }
        },
        messages: {
            email: {
                required: lang_field_required,
                email: lang_email_incorrect
            },
            password: {
                required: lang_field_required
            }

        },
        errorPlacement: function(error, element)
        {
            $(element).next().removeClass("hide-class");
            $(element).parent().append(error);
        },
        success: function(label)
        {
            var element = $(label).parent();
            element.find('label').addClass("hide-class");
        }
    });

    if (form.valid())
    {
    	$.post(CI_ROOT+"/login", form.serialize(), function(data)
    	{
    		var response = jQuery.parseJSON(data);

    		console.log(response);

    		if (response.success == 1)
    		{
    			window.location = response.url;
    		}
    		else
    		{
    			$("#formErrors").html('<div class="error-alert">'+response.message+'</div>');
    		}
    	});	
    }
}

function showImage(event, element)
{
    event.preventDefault();

    var reader = new FileReader();
    name = $(element).value;
    reader.onload = function (e) 
    {
        // e.target.result

        $("#imagePlace").html('<center><img src="'+e.target.result+'" /></center>');
        $(".btn-select-file").css("width", "68px !important");
        $("#upload_file").html("Change");
        $("#btn_delete_file").removeClass("hide-class");
    };
    reader.readAsDataURL($(element).prop("files")[0]);
}

function deleteFile(event, element)
{
    event.preventDefault();

    $("#imagePlace").html('');
    $("#upload_file").html("Select file");
    $(element).addClass("hide-class");
    $("input#input_category_pic").val('');
}

function addCategory(event, element)
{
    event.preventDefault();

    var form = $(element).closest('form');

    form.validate({
        debug: true,
        rules: {
            category_name: {
                required: true
            }
        },
        messages: {
            category_name: {
                required: lang_field_required
            }

        },
        errorPlacement: function(error, element)
        {
            $(element).next().removeClass("hide-class");
            $(element).parent().append(error);
        },
        success: function(label)
        {
            var element = $(label).parent();
            element.find('label').addClass("hide-class");
        }
    });

    if (form.valid())
    {   
        var file = $("input#input_category_pic").prop("files")[0];
        var form_data = new FormData();
        form_data.append('form_data', form.serialize())
        form_data.append('file', file)
        $.ajax({
            url: CI_ROOT+"/admin/saveCategory",    
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(data)
            {
                var response = jQuery.parseJSON(data);

                if (response.success == 1)
                {
                    form[0].reset();
                    $("#form_messages").html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
                    $("#imagePlace").html('');
                    $("#upload_file").html("Select file");
                    $("#btn_delete_file").addClass("hide-class");
                }
                else
                {
                    $("#form_messages").html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
                }
            }
        });
    }   
}

function editCategory(event, element, category_id)
{
    event.preventDefault();

    var form = $(element).closest('form');

    form.validate({
        debug: true,
        rules: {
            category_name: {
                required: true
            }
        },
        messages: {
            category_name: {
                required: lang_field_required
            }

        },
        errorPlacement: function(error, element)
        {
            $(element).next().removeClass("hide-class");
            $(element).parent().append(error);
        },
        success: function(label)
        {
            var element = $(label).parent();
            element.find('label').addClass("hide-class");
        }
    });

    if (form.valid())
    {   
        var file = $("input#input_edit_category_pic").prop("files")[0];
        var form_data = new FormData();
        form_data.append('form_data', form.serialize())
        form_data.append('category_id', category_id)
        form_data.append('file', file)
        $.ajax({
            url: CI_ROOT+"/admin/editCategory",    
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(data)
            {
                var response = jQuery.parseJSON(data);

                if (response.success == 1)
                {
                    $("#form_messages").html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
                }
                else
                {
                    $("#form_messages").html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
                }
            }
        });
    }   
}

function deleteCategory(event, category_id)
{
    event.preventDefault();

    var dt = $("#categoriesTable").DataTable();

    $.post(CI_ROOT+"/admin/deleteCategory", {category_id:category_id}, function(data)
    {
        var response = jQuery.parseJSON(data);

        if (response.success == 1)
        {
            $("#categories_messages").html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
            dt.draw(false);
        }
        else
        {
            $("#categories_messages").html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+response.message+'</div>');
        }
    });
}

function deleteCategoryImage(event, element, category_id)
{
    event.preventDefault();

    $.post(CI_ROOT+"/admin/deleteCategoryImage", {category_id:category_id}, function(data)
    {
        var response = jQuery.parseJSON(data);

        if (response.success == 1)
        {
            $(element).addClass("hide-class");
            $("#btn_download_file").addClass("hide-class");
            $("#imagePlace").html('');
            $("input#input_edit_category_pic").val('');
        }
        else
        {
            alert(response.message);
        }
    });
}