<?php

$reportdata['title'] = 'Client Rep Commissions Report for ' . $months[(int)$month] . ' ' . $year;
$reportdata['description'] = 'Commissions per employee per month based on their attached client invoice payment totals';
$reportdata['monthspagination'] = true;

$reportdata["tableheadings"] = array(
  "Employee",
  "Client",
  "Total Invoices",
  "Invoice Id",
  "Total Commissions",
);

$clientIdsWithCommissionsQuery = select_query('mod_client_account_commissions', 'tblClientsId', array('year' => $year, 'month' => $month));

$clientIdsWithCommissionsArray = array();
while($row = mysql_fetch_array($clientIdsWithCommissionsQuery))
{
  if(in_array($row['tblClientsId'], $clientIdsWithCommissionsArray))
  {
    continue;
  }else{
    $clientIdsWithCommissionsArray[] = $row['tblClientsId'];
  }
}

$adminCommissionsArray = array();
if($clientIdsWithCommissionsArray)
{
  foreach($clientIdsWithCommissionsArray as $clientId)
  {
    $clientInfoQuery = select_query('tblclients', "id, firstname, lastname, companyname", array('id' => $clientId));
    while($row = mysql_fetch_array($clientInfoQuery))
    {
      $clientInfoArray[$row['id']] = $row;
    }

    $commissionsFromClientQuery = select_query('mod_client_account_commissions', "tblInvoicesId, tblClientsId, tblAdminsId, commissionPercent, commissionAmount, year, month, day", array('tblClientsId' => $clientId, 'year' => (int)$year, 'month' => (int)$month));

    while($row = mysql_fetch_array($commissionsFromClientQuery))
    {
      $adminCommissionsArray[] = array(
        'adminId' => $row['tblAdminsId'],
        'invoiceId' => $row['tblInvoicesId'],
        'clientId' => $row['tblClientsId'],
        'commissionAmount' => $row['commissionAmount'],
        'year' => $row['year'],
        'month' => $row['month'],
        'day' => $row['day']
      );
    }
  }
}

$adminsGettingCommission = array();
if($adminCommissionsArray)
{
  foreach($adminCommissionsArray as $adminCommissions)
  {
    if(in_array($adminCommissions['adminId'], $adminsGettingCommission))
    {
      continue;
    }else{
      $adminsGettingCommission[] = $adminCommissions['adminId'];
    }
  }
}

if($adminsGettingCommission)
{
  foreach($adminsGettingCommission as $admin)
  {    
    $adminInfoQuery = select_query('tbladmins', "firstname, lastname, username", array('id' => $admin));
    while($row = mysql_fetch_array($adminInfoQuery))
    {
      $adminInfoArray = $row;
    }

    $totalCommission = 0.00;
    $totalInvoices = 0;
    foreach($adminCommissionsArray as $commission)
    {
      if($commission['adminId'] == $admin)
      {
        $totalCommission += $commission['commissionAmount'];
        $totalInvoices++;
      }
    }

    $reportdata['tablevalues'][] = array(
      $adminInfoArray['firstname'] . ' ' . $adminInfoArray['lastname'],
      '',
      $totalInvoices,
      '',
      $totalCommission
    );

    foreach($adminCommissionsArray as $commission)
    {
      if($commission['adminId'] == $admin)
      {
        if($clientInfoArray[$commission['clientId']]['companyname'] == '' )
        {
          $companyName = $clientInfoArray[$commission['clientId']]['lastname'] . ', ' . $clientInfoArray[$commission['clientId']]['firstname'];
        }else{
          $companyName = $clientInfoArray[$commission['clientId']]['companyname'];
        }

        $reportdata['tablevalues'][] = array(
          '',
          '<a href="clientssummary.php?userid=' . $commission['clientId'] . '" target="_blank">' . $companyName . '</a>',
          '',
          '<a href="invoices.php?action=edit&id=' . $commission['invoiceId'] . '" target="_blank">' . $commission['invoiceId'] . '</a>',
          $commission['commissionAmount']
        );
      }
    }
  }
}

