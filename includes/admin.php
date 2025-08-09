<?php
function sfl_admin_page() {
    ?>
    <div class="wrap">
        <h1>Lista de Arquivos Simples</h1>
        
        <?php
        // Handle form submissions
        if (isset($_POST['sfl_settings_submit'])) {
            update_option('sfl_max_files', intval($_POST['max_files']));
            update_option('sfl_max_size', intval($_POST['max_size']));
            update_option('sfl_allowed_types', sanitize_text_field($_POST['allowed_types']));
            
            echo '<div class="notice notice-success"><p>Configurações salvas com sucesso.</p></div>';
        }
        ?>
        
        <div class="sfl-admin-container">
            <div class="sfl-admin-content">
                <h2>Arquivos Enviados</h2>
                
                <?php sfl_render_admin_file_list(); ?>
            </div>
        </div>
    </div>
    <?php
}

function sfl_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configurações da Lista de Arquivos</h1>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="max_files">Máximo de Arquivos</label></th>
                    <td>
                        <input type="number" name="max_files" id="max_files" 
                               value="<?php echo esc_attr(get_option('sfl_max_files')); ?>" min="1" max="50">
                        <p class="description">Quantidade máxima de arquivos que podem ser enviados de uma vez.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_size">Tamanho Máximo do Arquivo (MB)</label></th>
                    <td>
                        <input type="number" name="max_size" id="max_size" 
                               value="<?php echo esc_attr(get_option('sfl_max_size')); ?>" min="1" max="20">
                        <p class="description">Tamanho máximo para cada arquivo enviado em megabytes.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="allowed_types">Tipos de Arquivo Permitidos</label></th>
                    <td>
                        <input type="text" name="allowed_types" id="allowed_types" 
                               value="<?php echo esc_attr(get_option('sfl_allowed_types')); ?>">
                        <p class="description">Lista separada por vírgulas das extensões permitidas (ex: jpg,png,pdf).</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Salvar Configurações', 'primary', 'sfl_settings_submit'); ?>
        </form>
    </div>
    <?php
}

function sfl_render_admin_file_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_file_list';
    
    $files = $wpdb->get_results("SELECT * FROM $table_name ORDER BY upload_date DESC");
    
    if (empty($files)) {
        echo '<p>Nenhum arquivo foi enviado ainda.</p>';
        return;
    }
    
    ?>
    <div class="sfl-file-list">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tamanho</th>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td><?php echo esc_html($file->file_name); ?></td>
                    <td><?php echo esc_html($file->file_size); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($file->upload_date)); ?></td>
                    <td><?php echo esc_html($file->description); ?></td>
                    <td><?php echo esc_html($file->category); ?></td>
                    <td>
                        <a href="<?php echo esc_url($file->file_url); ?>" target="_blank" class="button">Visualizar</a>
                        <a href="<?php echo esc_url($file->file_url); ?>" download class="button">Baixar</a>
                        <button class="button sfl-delete-file" data-file-id="<?php echo $file->id; ?>">Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}