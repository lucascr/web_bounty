<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="application/wasm; charset=utf-8">
        <title>Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="jquery-3.7.1.min.js"></script>
        <script src="waxjs.js"></script>
        <script src="https://unpkg.com/anchor-link@3"></script>
        <script src="https://unpkg.com/anchor-link-browser-transport@3"></script>
        <link rel="stylesheet" href="menu.css">
        <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-WCMYG5989D"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-WCMYG5989D');
</script>
</head>
<body>
    <p id="autologin"></p>
    <div id="divlogin">
      <button id="login" onclick=login_wax(false)>WAX Login</button>
      <button id="login" onclick=login_wax(true)>Anchor Login</button>
    </div>
    <div id="divlogout" style="display:none"><button id="loginout" onclick=logout()>Logout</button></div>

    <div id="divlists">
      <section>
      <div class="navbar">
        <div class="subnav">
        <button class="subnavbtn">Bounties <i class="fa fa-caret-down"></i></button>
          <div class="subnav-content">
            <a href="#link1">Add a Bounty</a>
            <a href="#list_bounty" id="list_bounty" onclick="list_bounty();">List Bounties</a>
          </div>
        </div>
        <div class="subnav">
        <button class="subnavbtn">Admin <i class="fa fa-caret-down"></i></button>
          <div class="subnav-content">
            <a href="#company">Add Aproover</a>
            <a href="#team">Remove Aprover</a>
          </div>
        </div>
      </div>
      </section>
      <div id="divlists_table"></div>
    </div>
    <script language="javascript">
    const contract = "bounty";
    const account = "bounty";

    function list_bounty(type){
        getBountySelector();
        getBountyTableHeader();
        getBountyTableData(type);
    }
    function getBountySelector(){
        $("#divlists_table").html("");
        $("#divlists_table").append("<select id='select_bounty' onchange='list_bounty(this.value);' class='subselect'><option value='0'>-Select-</option><option value='1'>New</option><option value='2'>Claimed</option><option value='3'>Approved</option><option value='4'>For Review</option><option value='5'>Reviewed</option><option value='6'>Finished</option><option value='7'>Cancelled</option><option value='0'>All</option></select>");
    }
    function getBountyTableHeader(){
        $("#divlists_table").append("<table id='table_bounty' class='table_bounty'><thead><tr><th>ID</th><th>Task</th><th>Criteria</th><th>Reward</th><th>Player</th><th>Options</th><th>status</th></tr></thead><tbody></tbody></table>");        
    }
    function getBountyTableData (type){
      
      // v1/chain/get_table_rows
      // {"json":true,"code":"bounty","scope":"bounty","table":"bounty","limit":100}
      <?php if ($_REQUEST['net']=="test") { ?>
        waxApiUrl ="https://wax-testnet.eosphere.io/";
        if(type=="0"){ //ALL         
          jsonData=JSON.stringify({"json":true,"code":"lucastestmin","scope":"lucastestmin","table":"bounties","limit":100});
        }else{ //NEW
          jsonData=JSON.stringify({"json":true,"code":"lucastestmin","scope":"lucastestmin","table":"bounties","index_position":"2","key_type":"i64","upper_bound":type,"lower_bound":type,"limit":100});
        }      
      <?php }else{ ?>

        waxApiUrl ="https://wax.eosphere.io/";
        if(type=="0"){ //ALL         
          jsonData=JSON.stringify({"json":true,"code":"bountiesgame","scope":"bountiesgame","table":"bounties","limit":100});
        }else{ //NEW
          jsonData=JSON.stringify({"json":true,"code":"bountiesgame","scope":"bountiesgame","table":"bounties","index_position":"2","key_type":"i64","upper_bound":type,"lower_bound":type,"limit":100});
        }
      <?php } ?>
      $.ajax({
        type: "POST",
        url: waxApiUrl+"v1/chain/get_table_rows",        
        data: jsonData,
        dataType: "json",
        success: function (data) {
          console.log(data.rows);
          $.each(data.rows, function (index, value) {
              console.log(value);
              $("#table_bounty tbody").append("<tr><td>"+value.bounty_id+"</td><td>"+value.task+"</td><td>"+value.criteria+"</td><td>"+value.reward+"</td><td>"+value.player+"</td><td><a href='#bounty_detail' onclick='bounty_detail(\""+value.id+"\");'>Detail</a></td><td>"+statusToString(value.state_bounty)+"</td></tr>");
          });
        },
        error: function (data) {
          console.log(data);
        }
      });
    }
    function statusToString(status){
      switch (parseInt(status)) {
        case 1:          return "New Bounty, Not Claimed";          break;
        case 2:          return "Claimed (Set by Player)";          break;
        case 3:          return "Approved (Set by Admin)";          break;
        case 4:          return "For Review (Set by Player)";          break;
        case 5:          return "Reviewed (Set by Admin)";          break;
        case 6:          return "Finished (payed)";          break;
        case 7:          return "Cancelled";          break;        
        default:          return "Unknown";          break;
      }
    }
    list_bounty("0");
    </script>
    <script>
    const dapp = "login";
    let login_use = "";
    let wallet_userAccount="none";

    const wax = new waxjs.WaxJS({
        rpcEndpoint: 'https://wax.greymass.com'
    });
    
    const transport = new AnchorLinkBrowserTransport();
    const anchorLink = new AnchorLink({
          transport,
          <?php if ($_REQUEST['net']=="test") { ?>
chains: [{
  chainId: 'f16b1833c747c43682f4386fca9cbb327929334a762755ebec17f6f23c9b8a12',
  nodeUrl: 'https://waxtestnet.greymass.com',
}]
<?php }else{ ?>
chains: [{
  chainId: '1064487b3cd1a897ce03ae5b6a865651747e2e152090f99c1d19d44e01aea5a4',
  nodeUrl: 'https://wax.greymass.com',
  
}]
<?php } ?>
});
    function login_wax(anchor){
      login_use=anchor;
      if (anchor) {
        login_anchor();
      }else{
        login_waxjs().then(function(retorno){
          wallet_userAccount=retorno;
          $("#autologin").html(wallet_userAccount);
          hideLoginButton();
        });        
      }
    }
    function login_anchor(){
        anchorLink.login(dapp).then((result) => {
          console.log(result);
          session = result.session;          
          console.log(result.session);
          wallet_userAccount=session.auth.actor;
          console.log(session.auth.actor);
          $("#autologin").html(String(wallet_userAccount).split("@")[0]);
          hideLoginButton();
        });
    }
    async function login_waxjs(){
      try {
        let userAccount = await wax.login();                
        return userAccount;
      } catch (e) {
        $("#autologin").html(e.message);        
      }
      return false;
    }
    function logout(){
      wallet_userAccount="";
      if(login_use){
        //anchorLink.logout();
        session.remove();        
        $("#autologin").html("logout");
      }else{
        wax.logout();
        $("#autologin").html("logout");
      }
        $("#divlogin").show();
        $("#divlogout").hide();
    }
    function hideLoginButton() {
      $("#divlogin").hide();
      $("#divlogout").show();
      loadMenu();
    }
    function loadMenu(){
      $("#divlists").html("");
    }
    </script>
    </body>
    </html>
