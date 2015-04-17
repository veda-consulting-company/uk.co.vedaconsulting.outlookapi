<h2>Outlook Auditlogs</h2><br />
<table id="outlook_audit" cellspacing="0" width="100%">
  <thead>
    <tr>
      <th>Date Time</th>
      <th>Entity</th>
      <th>Action</th>
      <th>Request</th>
      <th>Response</th>
    </tr>
  </thead>

  <tbody> 
    {foreach from=$outlookaudit item=audit}
      <tr>
        <td >{$audit.datetime}</td>
        <td >{$audit.action}</td>
        <td >{$audit.entity}</td>
        <td >{$audit.request}</td>
        <td >{$audit.response}</td>
      </tr>
    {/foreach}
  </tbody>    
</table>

{literal} 
<script> 
cj(document).ready(function() {
    var table = cj('#outlook_audit').DataTable( {
        scrollY:        "400px",
        scrollX:        true,
        scrollCollapse: true,
        paging:         false,
        columnDefs: [
            { width: '30%', targets: 0 }
        ]
    } );
    new cj.fn.dataTable.FixedColumns( table );
} );
</script>
{/literal}