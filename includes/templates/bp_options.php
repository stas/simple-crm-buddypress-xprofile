<form enctype="multipart/form-data" action="" method="post">
    <?php wp_nonce_field( 'scrm_bp', 'scrm_bp_nonce' ); ?>
    
    <?php if( $bp_installed ): ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'BuddyPress Simple CRM options','scrm_bp' )?>
                    <small>
                        <?php _e( 'This is not intended to replace the WordPress export option, it will only complete it!','scrm_bp' )?>
                    </small>
                </th>
                <td>
                    <?php if( !$export_file ): ?>
                        <input type="submit" name="scrm_bp_export" class="button-primary" value="<?php _e( 'Export' )?>"/>
                    <?php else: ?>
                        <a href="<?php echo $export_file; ?>" class="button-primary"><?php _e( 'Download' )?></a>
                        <input type="submit" name="scrm_bp_delete" class="button" value="<?php _e( 'Delete' )?>"/>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
    <?php else: ?>
        <div class="postbox">
            <h3 class="hndle" ><?php _e( 'Import BuddyPress XProfile Data','scrm_bp' )?></h3>
            <div class="inside">
                <p>
                    <?php _e( 'Upload the downloaded export file here.','scrm_bp' )?>
                    <br/>
                    <input type="file" name="scrm_bp_import_filename" />
                    <input type="submit" name="scrm_bp_import" class="button-primary" value="<?php _e( 'Import' )?>"/>
                </p>
            </div>
        </div>
    <?php endif; ?>
</form>

