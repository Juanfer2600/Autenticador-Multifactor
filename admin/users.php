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
                        <h3>Usuarios</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="pull-right">
                            <a id="adduser" class="btn btn-sm btn-primary"><i class="bx bx-plus bx-sm"></i></a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="users" class="table table-bordered table-striped table-sm responsive">
                            <thead class="text-center">
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Correo</th>
                                <th>Método de sesión</th>
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

    <div class="modal fade" id="modal_user" tabindex="-1" role="dialog" aria-labelledby="user_modal_label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="user_form">
                <div class="modal-content">
                    <div class="modal-header">
                        <b class="modal-title" id="user_modal_label"></b>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="user_crud" name="crud">
                        <input type="hidden" id="user_id" name="id">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="nombre_usuario">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="apellido_usuario">Apellido de usuario</label>
                                    <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="correo_usuario">Correo electrónico</label>
                                    <input type="text" class="form-control" id="correo_usuario" name="correo_usuario" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="metodos_mfa">Métodos MFA</label>
                            <select class="form-control" multiple="" id="metodos_mfa" name="metodos_mfa[]">
                                <option value="sms">SMS</option>
                                <option value="huella dactilar">huella dactilar</option>
                                <option value="reconocimiento facial">reconocimiento facial</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tipo_usuario">Tipo de usuario</label>
                            <select class="form-control" id="tipo_usuario" name="tipo_usuario" required>
                                <option value="">- Seleccionar -</option>
                                <?php
                                $sql = "SELECT * FROM user_type";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
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
    <?php include 'assets/users_back.php'; ?>
    <?php include 'assets/footer.php'; ?>
</body>

</html>