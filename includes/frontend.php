<?php
function sfl_render_upload_form() {
    if (!current_user_can('upload_files')) {
        return;
    }
    
    $max_files = get_option('sfl_max_files');
    $max_size = get_option('sfl_max_size');
    $allowed_types = get_option('sfl_allowed_types');
    
    ?>
    <div class="sfl-upload-container">
        <h2>Enviar Arquivos</h2>
        
        <div class="sfl-upload-area" id="sfl-drop-zone">
            <input type="file" id="sfl-file-input" multiple style="display: none;">
            <button id="sfl-browse-btn" class="sfl-browse-btn">Selecionar...</button>
            <p id="sfl-file-info">Nenhum arquivo selecionado.</p>
            
            <div class="sfl-upload-details">
                <p><strong>Limite de Arquivos:</strong> <?php echo $max_files; ?> arquivos</p>
                <p><strong>Tamanho Máximo:</strong> <?php echo $max_size; ?> MB por arquivo.</p>
                <p><strong>Tipos Permitidos:</strong> <?php echo $allowed_types; ?></p>
                <p>Arraste e solte arquivos aqui ou use o botão Selecionar.</p>
            </div>
            
            <div class="sfl-file-meta" style="display: none;">
                <div class="sfl-meta-fields">
                    <div>
                        <label for="sfl-file-description">Descrição:</label>
                        <input type="text" id="sfl-file-description" placeholder="Descrição do arquivo">
                    </div>
                    <div>
                        <label for="sfl-file-category">Categoria:</label>
                        <input type="text" id="sfl-file-category" placeholder="Categoria do arquivo">
                    </div>
                </div>
                <button id="sfl-upload-btn" class="sfl-upload-btn">Enviar</button>
            </div>
            
            <div class="sfl-progress" style="display: none;">
                <div class="sfl-progress-bar"></div>
                <span class="sfl-progress-text">0%</span>
            </div>
        </div>
    </div>
    <?php
}

function sfl_render_file_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_file_list';
    
    // Obtenha todas as categorias distintas
    $categorias = $wpdb->get_col("SELECT DISTINCT category FROM $table_name WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    $current_cat = isset($_GET['sfl_categoria']) ? sanitize_text_field($_GET['sfl_categoria']) : '';
    if ($current_cat) {
        $files = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE category = %s ORDER BY upload_date DESC", $current_cat));
    } else {
        $files = $wpdb->get_results("SELECT * FROM $table_name ORDER BY upload_date DESC");
    }
    
    if (empty($files)) {
        echo '<p>Nenhum arquivo disponível.</p>';
        return;
    }
    
    ?>
    <div class="sfl-file-list">
        <table>
            <thead>
                <tr>
                    <th colspan="7" style="background: #f7fafd; padding: 16px 15px;">
                        <form method="get" class="sfl-filter-form" style="margin:0; display: flex; align-items: center; gap: 10px;">
                            <label for="sfl_categoria" style="margin:0;"><strong>Filtrar por categoria:</strong></label>
                            <select name="sfl_categoria" id="sfl_categoria" onchange="this.form.submit()">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($current_cat, $cat); ?>><?php echo esc_html($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php
                            // Mantém outros parâmetros da URL (ex: página)
                            foreach ($_GET as $key => $value) {
                                if ($key !== 'sfl_categoria') {
                                    echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
                                }
                            }
                            ?>
                        </form>
                    </th>
                </tr>
                <tr>
                    <th>Miniatura</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Tamanho</th>
                    <th>Data</th>
                    <th>Abrir</th>
                    <th>Baixar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td style="text-align:center;">
                        <?php
                            $icon = sfl_get_file_icon(strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)));
                            if (in_array($icon, ['file-image'])) {
                                echo '<img src="' . esc_url($file->file_url) . '" alt="thumb" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">';
                            } else {
                                echo '<span class="sfl-file-icon dashicons dashicons-' . esc_attr($icon) . '" style="font-size:32px;color:#0073aa;"></span>';
                            }
                        ?>
                    </td>
                    <td>
                        <strong><?php echo esc_html($file->file_name); ?></strong>
                        <?php if (!empty($file->description)): ?>
                        <p class="sfl-file-description"><?php echo esc_html($file->description); ?></p>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($file->category); ?></td>
                    <td><?php echo esc_html($file->file_size); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($file->upload_date)); ?></td>
                    <td>
                        <a href="<?php echo esc_url($file->file_url); ?>" target="_blank" class="sfl-action-link" data-action="abrir">Abrir</a>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($file->file_url); ?>" download class="sfl-action-link" data-action="baixar">Baixar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}