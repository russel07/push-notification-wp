<?php
    wp_nonce_field( 'spnwp_save_additional_custom_fields', 'additional_custom_fields_nonce' );
?>

<style>
    .form-wrapper {
        width: 100%;
        padding: 0 1%;
    }

    .field-wrapper {
        margin-bottom: 20px;
    }

    .col-6 {
        flex: 1;
    }

    .field-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .field-group {

    }

    .input-label {
        font-size: 16px;
        font-weight: 500;
        font-family: ui-monospace;
        color: #585555;
        padding: 5px 2px;
    }

    .form-field input{
        height: 34px;
        border: thin solid #d2d2d2;
        border-radius: 5px;
        width: 94%;
    }

    .input-help {
        font-size: 13px;
        font-family: ui-monospace;
        line-height: 1.5rem;
        padding: 2px;
        color: #838080;
    }
</style>

<div class="form-wrapper">
    <div class="field-wrapper field-row">
        <div class="col-6 field-group">
            <label class="input-label">Call To Action</label>
            <div class="form-field">
                <input type="text" name="cta_one_label" class="cta_label" data-id="field_1" value="<?php echo isset($additional_fields['cta_one_label']) ? $additional_fields['cta_one_label'] : '';?>"/>
            </div>
            <span class="input-help">This value will be used as a button label</span>
        </div>

        <div class="col-6 field-group">
            <label class="input-label">Action Value</label>
            <div class="form-field">
                <input type="text" name="cta_one_value" class="cta_value" id="cta_value_field_1" value="<?php echo isset($additional_fields['cta_one_value']) ? $additional_fields['cta_one_value'] : '';?>"/>
            </div>
            <span class="input-help">Redirect URL if clicked on the button</span>
        </div>
    </div>

    <div class="field-wrapper field-row">
        <div class="col-6">
            <label class="input-label">Call To Action</label>
            <div class="form-field">
                <input type="text" name="cta_two_label" class="cta_label" data-id="field_2" value="<?php echo isset($additional_fields['cta_two_label']) ? $additional_fields['cta_two_label'] : '';?>"/>
            </div>
            <span class="input-help">This value will be used as a button label</span>
        </div>

        <div class="col-6">
            <label class="input-label">Action Value</label>
            <div class="form-field">
                <input type="text" name="cta_two_value" class="cta_value" id="cta_value_field_2" value="<?php echo isset($additional_fields['cta_two_value']) ? $additional_fields['cta_two_value'] : '';?>"/>
            </div>
            <span class="input-help">Redirect URL if clicked on the button</span>
        </div>
    </div>

    <div class="field-wrapper field-row">
        <div class="col-6">
            <label class="input-label">Start Date</label>
            <div class="form-field">
                <input type="date" name="notification_start" class="notification_start" value="<?php echo isset($additional_fields['notification_start']) ? $additional_fields['notification_start'] : '';?>"/>
            </div>
            <span class="input-help">By default notification will show once you published.</span>
        </div>

        <div class="col-6">
            <label class="input-label">End Date</label>
            <div class="form-field">
                <input type="date" name="notification_end" class="notification_end" value="<?php echo isset($additional_fields['notification_end']) ? $additional_fields['notification_end'] : '';?>"/>
            </div>
            <span class="input-help">If you want to disapper this after a period of time.</span>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.cta_label').keyup(function(){
            let val = $(this).val();
            let id = $(this).data("id");
            let targetInput = $('#cta_value_' + id);

            if(val) {
                targetInput.attr("required", true);
            } else {
                targetInput.removeAttr("required");
            }
        });
    });
</script>