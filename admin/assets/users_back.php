<script>
    $(document).ready(function() {
        var table = $('#user').DataTable({
            ajax: {
                url: 'includes/users_crud.php',
                type: 'GET',
                data: function(d) {
                    d.action = 'fetch';
                }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'nombre_usuario'
                },
                {
                    data: 'correo_usuario'
                },
                {
                    data: 'metodos_mfa'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                }
            ],
            autoWidth: false,
            ordering: false,
            stateSave: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            }
        });

        $('#adduser').on('click', function() {
            $('#user_form')[0].reset();
            $('#user_crud').val('create');
            $('#user_modal_label').text('Agregar usuario');
            $('#modal_user').modal('show');
        });

        $('#user').on('click', 'edit', function(){
            var id = $(this).data('id');
            $('#user_form')[0].reset();
            $('#user_crud').val('edit');
            $('#user_id').val(id);
            $('#user_modal_label').text('Editar usuario');
            $.ajax({
                url: 'includes/users_crud.php',
                type: 'POST',
                data: {
                    action: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response){
                    $('#nombre_usuario').val(response.nombre_usuario);
                    $('#correo_usuario').val(response.correo_usuario);
                    $('#password').val(response.password);
                    $('#modal_user').modal('show');
                }
            });
        });
    });
</script>