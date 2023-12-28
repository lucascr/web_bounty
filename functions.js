
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
    $("#divlists_table").append("<table id='table_bounty' class='table_bounty'><thead><tr><th>Title</th><th>Description</th><th>Reward</th><th>Contractor</th><th>Options</th><th>status</th></tr></thead><tbody></tbody></table>");        
}
function getBountyTableData (type){
  waxApiUrl ="https://wax-testnet.eosphere.io/";
  // v1/chain/get_table_rows
  // {"json":true,"code":"bounty","scope":"bounty","table":"bounty","limit":100}
  if(type=="0"){ //ALL         
    jsonData=JSON.stringify({"json":true,"code":"lucastestmin","scope":"lucastestmin","table":"bounties","limit":100});
  }else{ //NEW
    jsonData=JSON.stringify({"json":true,"code":"lucastestmin","scope":"lucastestmin","table":"bounties","index_position":"2","key_type":"i64","upper_bound":type,"lower_bound":type,"limit":100});
  }
  $.ajax({
    type: "POST",
    url: waxApiUrl+"v1/chain/get_table_rows",        
    data: jsonData,
    dataType: "json",
    success: function (data) {
      console.log(data.rows);
      $.each(data.rows, function (index, value) {
          $("#table_bounty tbody").append("<tr><td>"+value.title+"</td><td>"+value.description.substring(0, 30)+"</td><td>"+value.reward+"</td><td>"+value.contractor+"</td><td><a href='#bounty_detail' onclick='bounty_detail(\""+value.id+"\");'>Detail</a></td><td>"+statusToString(value.state_bounty)+"</td></tr>");
      });
    },
    error: function (data) {
      console.log(data);
    }
  });
}
function statusToString(status){
  switch (parseInt(status)) {
    case 1:          return "New Bounty , not claimed";          break;
    case 2:          return "Claimed (Set by contractor)";          break;
    case 3:          return "Approved (Set by approver)";          break;
    case 4:          return "For Review (Set by contractor)";          break;
    case 5:          return "Reviewed (Set by reviewer)";          break;
    case 6:          return "Finished (payed)";          break;
    case 7:          return "Cancelled";          break;        
    default:          return "Unknown";          break;
  }
}
list_bounty("0");

const dapp = "login";
let login_use = "";
let wallet_userAccount="none";

const wax = new waxjs.WaxJS({
    rpcEndpoint: 'https://wax.greymass.com'
});

const transport = new AnchorLinkBrowserTransport();
/* mainnet
chains: [{
chainId: '1064487b3cd1a897ce03ae5b6a865651747e2e152090f99c1d19d44e01aea5a4',
nodeUrl: 'https://wax.greymass.com',
}]*/
const anchorLink = new AnchorLink({
      transport,
chains: [{
chainId: 'f16b1833c747c43682f4386fca9cbb327929334a762755ebec17f6f23c9b8a12',
nodeUrl: 'https://waxtestnet.greymass.com',
}]
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