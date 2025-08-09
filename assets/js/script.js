jQuery(document).ready(function($) {
    // Handle file selection
    $('#sfl-browse-btn').on('click', function(e) {
        e.preventDefault();
        $('#sfl-file-input').click();
    });
    
    $('#sfl-file-input').on('change', function() {
        const files = this.files;
        if (files.length > 0) {
            const fileNames = Array.from(files).map(file => file.name).join(', ');
            $('#sfl-file-info').text(`${files.length} arquivo(s) selecionado(s): ${fileNames}`);
            $('.sfl-file-meta').show();
        } else {
            $('#sfl-file-info').text('Nenhum arquivo selecionado.');
            $('.sfl-file-meta').hide();
        }
    });
    
    // Handle drag and drop
    const dropZone = $('#sfl-drop-zone')[0];
    
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        dropZone.addEventListener('dragleave', function() {
            $(this).removeClass('drag-over');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                $('#sfl-file-input')[0].files = files;
                const fileNames = Array.from(files).map(file => file.name).join(', ');
                $('#sfl-file-info').text(`${files.length} file(s) selected: ${fileNames}`);
                $('.sfl-file-meta').show();
            }
        });
    }
    
    // Handle file upload
    $('#sfl-upload-btn').on('click', function(e) {
        e.preventDefault();
        
        const files = $('#sfl-file-input')[0].files;
        if (!files || files.length === 0) {
            alert('Selecione pelo menos um arquivo para enviar.');
            return;
        }
        
        const maxFiles = parseInt('<?php echo get_option("sfl_max_files"); ?>');
        if (files.length > maxFiles) {
            alert(`Você pode enviar no máximo ${maxFiles} arquivos de uma vez.`);
            return;
        }
        
        const description = $('#sfl-file-description').val();
        const category = $('#sfl-file-category').val();
        
        $('.sfl-progress').show();
        
        const formData = new FormData();
        formData.append('action', 'sfl_upload_file');
        formData.append('security', sfl_ajax.nonce);
        formData.append('description', description);
        formData.append('category', category);
        
        for (let i = 0; i < files.length; i++) {
            formData.append('sfl_file_upload', files[i]);
        }
        
        $.ajax({
            url: sfl_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        $('.sfl-progress-bar').css('width', percent + '%');
                        $('.sfl-progress-text').text(percent + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    alert('Arquivos enviados com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Erro: ' + error);
            },
            complete: function() {
                $('.sfl-progress').hide();
                $('.sfl-progress-bar').css('width', '0%');
                $('.sfl-progress-text').text('0%');
            }
        });
    });
    
    // Handle file deletion
    $('.sfl-delete-file').on('click', function() {
        if (!confirm('Tem certeza que deseja excluir este arquivo?')) {
            return;
        }
        
        const fileId = $(this).data('file-id');
        
        $.ajax({
            url: sfl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sfl_delete_file',
                security: sfl_ajax.nonce,
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    alert('Arquivo excluído com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Erro: ' + error);
            }
        });
    });
    
    // Handle copy link
    $('.sfl-copy-link').on('click', function(e) {
        e.preventDefault();
        const fileUrl = $(this).data('file-url');
        
        navigator.clipboard.writeText(fileUrl).then(function() {
            alert('Link copiado para a área de transferência!');
        }, function() {
            alert('Falha ao copiar o link. Tente novamente.');
        });
    });
});