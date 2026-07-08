function loadSessionItems(route_url , token , academic_session_id, selected_session_item_id = null)
{
   
    $.ajax({
        url : route_url,
        method : "POST",
        dataType: "json",
        data:{
            _token: token,
            academic_session_id: academic_session_id
        },
        success: function(response){
            if(response.status_code === 200)
            {
                $('#session_item_id').html('<option value="">Select</option>');
                $.each(response.session_items, function(index, item){
                    let selected = '';
                    if(item.id == selected_session_item_id){
                        selected = 'selected';
                    }
                    $('#session_item_id').append(
                        '<option value="'+item.id+'" '+selected+'>Class '+item.class+' - Section '+item.section+'</option>'
                    );
                });
            }
        }
    });
}