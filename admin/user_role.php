<?php 
include 'assets/header.php'; 
include 'assets/session.php';
?>

<body>
    <div id="layout-wrapper">
        <?php include 'assets/topbar.php'; ?>
        <?php include 'assets/menubar.php'; ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="content-header">
                        <h3>Roles</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="pull-right">
                            <a id="adduser_role" class="btn btn-sm btn-primary"><i class="bx bx-plus bx-sm"></i></a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="user_role" class="table table-bordered table-striped table-sm responsive">
                            <thead class="text-center">
                                <th>ID</th>
                                <th>Tipo de usuario</th>
                                <th>Acciones</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="user_role_modal" tabindex="-1" role="dialog" aria-labelledby="user_role_modal_label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="user_role_form">
                <div class="modal-content">
                    <div class="modal-header">
                        <b class="modal-title" id="user_role_modal_label"></b>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="user_role_crud" name="crud">
                        <input type="hidden" id="user_role_id" name="id">

                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><i class="fa fa-times"></i>
                            Cerrar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'assets/scripts.php'; ?>
    <?php include 'assets/user_role_back.php'; ?>
    <?php include 'assets/footer.php'; ?>

</body>

</html>