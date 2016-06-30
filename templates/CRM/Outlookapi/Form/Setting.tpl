<div class="crm-block crm-form-block crm-outlook-setting-form-block">
  <div class="crm-accordion-wrapper crm-accordion_mailchimp_setting-accordion crm-accordion-open">
    <div class="crm-accordion-header">
      <div class="icon crm-accordion-pointer"></div>
      {ts}Outlook Integration with CiviCRM Settings{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">

      <table class="form-layout-compressed">
    	  <tr class="crm-outlook-setting-activity-types-block">
          <td class="label">{$form.activity_type.label}</td>
          <td>{$form.activity_type.html}<br/>
      	    <span class="description">{ts}(Default activity type for all the emails filed from Outlook){/ts}
	          </span>
          </td>
        </tr>
      </table>
    </div>
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl"}
    </div>
  </div>
</div>

{*script*}
{literal}

<script type="text/javascript">
 cj(document).ready(function(){
 });

</script>
{/literal}
{*end*}
