<?php

function hook_credit_employee_commission($vars)
{

  $invoiceId = $vars['invoiceid'];
  $year = date("Y");
  $month = date("m");
  $day = date("d");

  $getClientsIdQuery = select_query('tblinvoices', "userid, total", array('id' => $invoiceId));
  while($row = mysql_fetch_array($getClientsIdQuery, MYSQL_ASSOC))
  {
    $clientsId = $row['userid'];
    $invoiceTotal = $row['total'];
  }

  $checkClientMappingQuery = select_query('mod_client_account_rep', "tblAdminsId, commissionPercent", array('tblClientsId' => $clientsId));
  while($row = mysql_fetch_array($checkClientMappingQuery, MYSQL_ASSOC))
  {
    $adminsId = $row['tblAdminsId'];
    $commissionPercent = $row['commissionPercent'];
  }

  if($adminsId && $commissionPercent)
  {
    $commissionAmount = ($commissionPercent / 100) * $invoiceTotal;

    $insertArray = array(
      'tblInvoicesId' => $invoiceId,
      'tblClientsId' => $clientsId,
      'tblAdminsId' => $adminsId,
      'commissionPercent' => $commissionPercent,
      'commissionAmount' => $commissionAmount,
      'year' => $year,
      'month' => $month,
      'day' => $day
    );
 
    $insertCommissionQuery = insert_query('mod_client_account_commissions', $insertArray);
  }
}

add_hook('InvoicePaid', 1, 'hook_credit_employee_commission');
