<script>
    $(document).ready(function() {
        var table = $('#users').DataTable({
            ajax: {
                url: 'assets/includes/users_crud.php',
                type: 'GET',
                data: function(d) {
                    d.crud= 'fetch';
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
            $('#metodos_mfa').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal_user')
            });
        });

        // Fix the edit button selector
        $('#users').on('click', '.edit', function() {
            var id = $(this).data('id');
            $('#user_form')[0].reset();
            $('#user_crud').val('edit');
            $('#user_id').val(id);
            $('#user_modal_label').text('Editar usuario');
            $.ajax({
                url: 'assets/includes/users_crud.php',
                type: 'POST',
                data: {
                    crud: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    $('#nombre_usuario').val(response.nombre_usuario || '');
                    $('#correo_usuario').val(response.correo_usuario || '');
                    $('#password').val(response.password || '');
                    $('#metodos_mfa').val(response.metodos_mfa.split(', ')).trigger('change');
                    $('#modal_user').modal('show');
                    $('#metodos_mfa').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#modal_user')
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Ajax Error:', xhr.responseText);
                    Swal.fire('Error', 'Ha ocurrido un error al obtener los datos', 'error');
                }
            });
        });

        $('#users').on('click', '.delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'assets/includes/users_crud.php',
                        data: {
                            action: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire(response.message, '', response.status ? 'success' : 'error');
                            $('#users').DataTable().ajax.reload(null, false);
                        }
                    });
                }
            });
        });

        $('#user_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: 'assets/includes/users_crud.php',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $('#modal_user').modal('hide');
                    Swal.fire(response.message, '', response.status ? 'success' : 'error');
                    $('#users').DataTable().ajax.reload(null, false);
                }
            });
        });
    });
</script>