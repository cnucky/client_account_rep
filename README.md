# client_account_rep
Map clients to employees/admins and set an optional commission percentage

###Installation
1. cd whmcs/modules/addons/
2. git clone git@github.com:mikeyb/client_account_rep.git
3. cd client_account_rep
4. cp client_account_rep_report.php ../../reports/
5. Login to WHMCS Admin panel and click on Setup -> Addon Modules
6. Activate Client Account Rep plugin
7. Click Configure and check Full Administrator

###Usage
Map each client to an employee with optional commission % under Addons -> Client Account Rep.  Each time an invoice is marked 'Paid' the commission will be calculated and saved.  Commissions can be viewed by month under Reports -> Other -> Client Account Rep Report.

###Screenshots
![client_account_rep_addon](https://downsouth.hosting/screenshots/addons/client_account_rep/client_account_rep_addon.png)
Main Addon Screen

![client_account_rep_addon](https://downsouth.hosting/screenshots/addons/client_account_rep/client_account_rep_report.png)
Report Screen
