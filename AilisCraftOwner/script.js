const STORAGE_KEY = "ailisCraftDashboard_v2";
const AUTH_KEY = "ailisCraftOwnerAuth";

if (localStorage.getItem(AUTH_KEY) !== "true") {
  window.location.replace("login.php");
}

const defaultState = {
  settings: { notifications: true, autoBackup: true, guestCheckout: true },
  products: [
    {id:"flower_tulip_trio", name:"Sunflower Trio Bouquet", category:"Flowers", price:480, stock:12, image:"assets/products/flower1.png", emoji:"💐", desc:"3x crochet flowers with pink paper wrap."},
    {id:"flower_rose_bunny", name:"Rose & Bunny Bouquet", category:"Flowers", price:480, stock:10, image:"assets/products/flower3.png", emoji:"🌹", desc:"Rose flower with mini crochet bunny and kraft wrap."},
    {id:"flower_sunflower", name:"Sunflower Wrap", category:"Flowers", price:480, stock:10, image:"assets/products/flower2.png", emoji:"🌻", desc:"Crochet sunflower with eucalyptus stems."},
    {id:"flower_cherry_blossom", name:"Cherry Blossom Mix", category:"Flowers", price:480, stock:9, image:"assets/products/flower4.jpg", emoji:"🌸", desc:"Sakura stems with white lace ribbon."},
    {id:"bag_mini", name:"Green Classic Sling Bag", category:"Bags", price:350, stock:8, image:"assets/products/bag1.png", emoji:"👜", desc:"Mini handmade bag with cream yarn and button strap."},
    {id:"bag_purse", name:"Coin Purse", category:"Bags", price:380, stock:12, image:"assets/products/bag2.jpg", emoji:"👛", desc:"Handheld purse with zipper closure."},
    {id:"bag_sling", name:"Beach Bag", category:"Bags", price:420, stock:7, image:"assets/products/bag3.png", emoji:"👜", desc:"Long crossbody strap with flower patch."},
    {id:"head_clips", name:"Pink Bow Barrette Band", category:"Head Wears", price:75, stock:16, image:"assets/products/headwear1.png", emoji:"🎀", desc:"Set of 2 clips in pastel colors."},
    {id:"head_headband", name:"Headband", category:"Head Wears", price:85, stock:14, image:"assets/products/headwear2.png", emoji:"🌷", desc:"Flexible crochet headband with floral styling."},
    {id:"head_bandana", name:"Bandana", category:"Head Wears", price:95, stock:13, image:"assets/products/headwear3.png", emoji:"🧢", desc:"Tie-style granny square bandana."},
    {id:"key_capybara", name:"Pink Star Flower Keychain", category:"Keychains", price:150, stock:20, image:"assets/products/keychain1.png", emoji:"🔑", desc:"Decorative handmade keychain with metal ring."},
    {id:"key_bunny", name:"Bunny Charm", category:"Keychains", price:150, stock:18, image:"assets/products/keychain2.png", emoji:"🐰", desc:"Mini bunny charm with pastel pink ribbon."},
    {id:"key_cat", name:"Cat Charm", category:"Keychains", price:150, stock:18, image:"assets/products/keychain3.png", emoji:"🐱", desc:"White kitty charm with bell accessory."}
  ],
  customers: [
    {id:"C001", name:"Maria Santos", email:"maria@example.com", phone:"09171234567", orders:4},
    {id:"C002", name:"Juan Dela Cruz", email:"juan@example.com", phone:"09181234567", orders:3},
    {id:"C003", name:"Angela Reyes", email:"angela@example.com", phone:"09201234567", orders:2},
    {id:"C004", name:"Nicole Garcia", email:"nicole@example.com", phone:"09221234567", orders:5},
    {id:"C005", name:"Mark Villanueva", email:"mark@example.com", phone:"09351234567", orders:1}
  ],
  orders: [
    {id:"ORD-1001", customer:"Maria Santos", product:"Crochet Flower Bouquet", qty:1, total:450, customization:"Pink flowers, white ribbon", status:"Delivered", date:"2026-08-28"},
    {id:"ORD-1002", customer:"Juan Dela Cruz", product:"Custom Crochet Keychain", qty:2, total:300, customization:"Blue + initials JD", status:"Preparing", date:"2026-08-28"},
    {id:"ORD-1003", customer:"Angela Reyes", product:"Fuzzy Wire Bouquet", qty:1, total:520, customization:"Purple flowers", status:"Preparing", date:"2026-08-29"},
    {id:"ORD-1004", customer:"Nicole Garcia", product:"Crochet Tote Bag", qty:1, total:650, customization:"Cream + brown strap", status:"Preparing", date:"2026-08-29"},
    {id:"ORD-1005", customer:"Mark Villanueva", product:"Mini Crochet Plushie", qty:2, total:560, customization:"Green + yellow", status:"Preparing", date:"2026-08-29"},
    {id:"ORD-1006", customer:"Nicole Garcia", product:"Fuzzy Wire Tulip", qty:3, total:540, customization:"Mixed colors", status:"Delivered", date:"2026-08-27"}
  ],
  reviews: []
};

let state = loadState();
state.reviews = []; // Reviews are sourced from product_reviews in MySQL, never demo/local data.
let currentPage = "dashboard";

function loadState(){
  let loaded;
  try{
    const saved = localStorage.getItem(STORAGE_KEY);
    if(saved) loaded = JSON.parse(saved);
  }catch(e){}
  const data = loaded || JSON.parse(JSON.stringify(defaultState));

  // Keep older saved data compatible with the new three-stage order workflow.
  if(Array.isArray(data.orders)){
    data.orders.forEach(order => {
      if(order.status === "Pending" || order.status === "Processing" || order.status === "Ready"){
        order.status = "Preparing";
      }else if(order.status === "Completed"){
        order.status = "Delivered";
      }
    });
  }
  return data;
}
function saveState(){ localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); }
function money(n){ return "₱" + Number(n).toLocaleString("en-PH",{minimumFractionDigits:2,maximumFractionDigits:2}); }
function escapeHtml(s){ return String(s ?? "").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m])); }
function statusClass(s){ return String(s).toLowerCase().replace(/\s+/g,"-"); }
function toast(msg,type="success"){
  const el=document.createElement("div"); el.className="toast "+type; el.textContent=msg;
  document.getElementById("toastContainer").appendChild(el);
  setTimeout(()=>el.remove(),2600);
}
function pageTitle(title,desc,actions=""){
  return `<div class="page-title"><div><h1>${title}</h1><p>${desc}</p></div><div class="actions">${actions}</div></div>`;
}
function stat(label,value,change,icon,cls){
  return `<div class="stat-card"><div class="stat-top"><div class="stat-icon ${cls}">${icon}</div></div><div class="stat-label">${label}</div><div class="stat-value">${value}</div><div class="stat-change ${change.startsWith("-")?"down":"up"}">${change}</div></div>`;
}
const ORDER_API = "../AilisCraftUser/api/orders.php";
async function syncOrdersFromDatabase(showErrors=false) {
  try {
    const response = await fetch(ORDER_API + "?action=owner-list", {headers:{"Accept":"application/json"}, cache:"no-store"});
    const data = await response.json();
    if (!response.ok || !data.ok) throw new Error(data.error || "Unable to load database orders.");
    state.orders = (data.orders || []).map(o => {
      const items = Array.isArray(o.items) ? o.items : [];
      const customization = items.map(item => {
        const d = item.customization || {}, bits = [];
        if (Array.isArray(item.options)) bits.push(...item.options);
        if (d.specialRequest) bits.push("Special request: " + d.specialRequest);
        if (d.letter && d.letter.message) bits.push((d.letter.type || "Letter") + ": " + d.letter.message);
        return (item.name || "Item") + " × " + (item.quantity || 1) + (bits.length ? " — " + bits.join("; ") : "");
      }).join("\n");
      return {...o, customer:o.customerName || o.payerName || "Customer", email:o.customerEmail || "",
        phone:o.customerPhone || o.contact || "", product:items.map(i=>i.name).join(", ") || "Order",
        qty:items.reduce((sum,i)=>sum+Number(i.quantity||1),0), customization:customization || "",
        status:o.status || "Preparing", total:Number(o.total||0), date:o.date?String(o.date).slice(0,10):"",
        snapshot:(items.length && items[0].snapshot) ? items[0].snapshot : "", items};
    });
    const map = new Map();
    state.orders.forEach(o => {
      const key = String(o.email || o.customer).toLowerCase();
      if (!map.has(key)) map.set(key,{id:o.dbId||key,name:o.customer,email:o.email||"",phone:o.phone||"",orders:0});
      map.get(key).orders++;
    });
    if (map.size) state.customers = Array.from(map.values());
    saveState(); render(); return true;
  } catch(error) {
    if (showErrors) toast("Database orders could not be loaded: " + error.message, "error");
    return false;
  }
}

function render(){
  renderDashboard(); renderOrders(); renderProducts(); renderCustomers(); renderPayments(); renderReviews(); renderReports(); renderSettings();
  document.querySelectorAll(".page").forEach(p=>p.classList.remove("active"));
  document.getElementById("page-"+currentPage).classList.add("active");
  document.querySelectorAll(".nav-item").forEach(b=>b.classList.toggle("active",b.dataset.page===currentPage));
  document.getElementById("pendingBadge").textContent=state.orders.filter(o=>o.status==="Preparing").length;
}
function renderDashboard(){
  const revenue=state.orders.filter(o=>o.status!=="Cancelled").reduce((a,o)=>a+o.total,0);
  const pending=state.orders.filter(o=>o.status==="Preparing").length;
  const completed=state.orders.filter(o=>o.status==="Completed").length;
  const avg=state.reviews.length?Math.round(state.reviews.reduce((a,r)=>a+Number(r.rating||0),0)/state.reviews.length*10)/10:0;
  const bars=[55,72,61,84,68,93,76,60,70,88,75,98];
  document.getElementById("page-dashboard").innerHTML =
    pageTitle("Dashboard","Overview of Aili's Craft ordering and personalization system.",
      `<button class="btn btn-light" onclick="exportData()">Export Data</button><button class="btn btn-primary" onclick="openOrderModal()">+ New Order</button>`) +
    `<div class="stats">
      ${stat("Total Revenue",money(revenue),"+12.4% vs last month","₱","icon-purple")}
      ${stat("Total Orders",state.orders.length,"+8.2% vs last month","🛍","icon-blue")}
      ${stat("Preparing Orders",pending,pending?"Needs attention":"All clear","⏱","icon-orange")}
      ${stat("Average Rating",avg+"/5",state.reviews.length+" customer reviews","★","icon-green")}
    </div>
    <div class="grid-main">
      <section class="panel"><div class="panel-head"><h3>Sales Overview</h3><span>Last 12 days</span></div>
        <div class="chart-wrap"><div class="chart">${bars.map((h,i)=>`<div class="bar-group"><div class="bar ${i%2===0?"current":""}" style="height:${h}%"></div><span class="bar-label">${i+1}</span></div>`).join("")}</div>
        <div class="chart-legend"><span><i class="dot"></i>Current period</span><span><i class="dot gray"></i>Previous period</span></div></div>
      </section>
      <section class="panel"><div class="panel-head"><h3>Order Time</h3><span>August</span></div>
        <div class="donut-area"><div class="donut"></div><div class="donut-center"><strong>${state.orders.length}</strong><small>orders</small></div></div>
        <div class="legend-list"><div class="legend-row"><span class="left"><i class="legend-color" style="background:#7667e8"></i>Afternoon</span><strong>42%</strong></div><div class="legend-row"><span class="left"><i class="legend-color" style="background:#9e92f0"></i>Evening</span><strong>32%</strong></div><div class="legend-row"><span class="left"><i class="legend-color" style="background:#e2dfff"></i>Morning</span><strong>26%</strong></div></div>
      </section>
    </div>
    <div class="grid-bottom">
      <section class="panel"><div class="panel-head"><h3>Customer Satisfaction</h3><span>Based on reviews</span></div><div class="panel-body">
        <div class="metric-list">
          <div class="metric"><div class="metric-head"><span>5-star reviews</span><strong>${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)===5).length/state.reviews.length*100):0}%</strong></div><div class="progress"><i style="width:${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)===5).length/state.reviews.length*100):0}%"></i></div></div>
          <div class="metric"><div class="metric-head"><span>4-star reviews</span><strong>${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)===4).length/state.reviews.length*100):0}%</strong></div><div class="progress"><i style="width:${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)===4).length/state.reviews.length*100):0}%"></i></div></div>
          <div class="metric"><div class="metric-head"><span>3 stars or lower</span><strong>${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)<=3).length/state.reviews.length*100):0}%</strong></div><div class="progress"><i style="width:${state.reviews.length?Math.round(state.reviews.filter(r=>Number(r.rating)<=3).length/state.reviews.length*100):0}%"></i></div></div>
        </div></div>
      </section>
      <section class="panel"><div class="panel-head"><h3>Most Ordered Products</h3><button class="btn btn-light" onclick="navigate('products')">View All</button></div><div class="panel-body"><div class="rating-list">
        ${state.products.slice(0,4).map((p,i)=>`<div class="rating-item"><div class="product-thumb">${p.image?`<img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" loading="lazy">`:`${p.emoji||"🧶"}`}</div><div><strong>${escapeHtml(p.name)}</strong><small>${p.category}</small></div><span class="stars">${(()=>{const rs=state.reviews.filter(r=>r.productCode&&r.productCode===p.id);const a=rs.length?rs.reduce((n,r)=>n+Number(r.rating||0),0)/rs.length:0;return a?`★ ${a.toFixed(1)}`:"No ratings";})()}</span></div>`).join("")}
      </div></div></section>
    </div>
    <section class="panel table-panel" style="margin-top:18px"><div class="panel-head"><h3>Recent Orders</h3><button class="btn btn-light" onclick="navigate('orders')">View Report</button></div>${ordersTable(state.orders.slice(-5).reverse())}</section>`;
}
function ordersTable(orders){
  if(!orders.length) return `<div class="empty">No orders found.</div>`;
  return `<div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Snapshot</th><th>Customer</th><th>Product</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>
  ${orders.map(o=>`<tr class="order-row" role="button" tabindex="0" onclick="viewOrder('${o.id}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();viewOrder('${o.id}')}"><td><strong>${escapeHtml(o.id)}</strong></td><td>${orderThumbSrc(o)?`<img class="order-snapshot-thumb" src="${orderThumbSrc(o)}" alt="Order snapshot" title="Click to enlarge" onclick="event.stopPropagation();openSnapshot(this.src)" onerror="this.onerror=null;this.src='${fallbackAssetSrc(o.product||"")}'">`:`<span class="cell-muted">&mdash;</span>`}</td><td>${escapeHtml(o.customer)}</td><td>${escapeHtml(o.product)}</td><td>${money(o.total)}</td><td><span class="status ${statusClass(o.status)}">${escapeHtml(o.status)}</span></td><td>${escapeHtml(o.date)}</td></tr>`).join("")}
  </tbody></table></div>`;
}
function renderOrders(){
  document.getElementById("page-orders").innerHTML =
    pageTitle("Orders","Click an order to view its details and update its status.") +
    `<section class="panel table-panel"><div class="table-tools"><select class="filter" id="orderFilter" onchange="renderOrdersTable()"><option>All Status</option><option>Preparing</option><option>Shipped</option><option>Out for Delivery</option><option>Delivered</option><option>Cancelled</option></select><span class="spacer"></span><button class="btn btn-light" onclick="exportOrders()">Export CSV</button></div><div id="ordersTableMount">${ordersTable(state.orders)}</div></section>`;
}
function renderOrdersTable(){
  const filter=document.getElementById("orderFilter")?.value||"All Status";
  const q=document.getElementById("globalSearch").value.toLowerCase();
  const rows=state.orders.filter(o=>(filter==="All Status"||o.status===filter)&&[o.id,o.customer,o.product,o.customization].join(" ").toLowerCase().includes(q));
  document.getElementById("ordersTableMount").innerHTML=ordersTable(rows);
}
function renderProducts(){
  document.getElementById("page-products").innerHTML =
    pageTitle("Products","Manage handmade products, prices, stock, and customization-ready items.",
      `<button class="btn btn-primary" onclick="openProductModal()">+ Add Product</button>`) +
    `<div class="product-grid">${state.products.map(p=>`<div class="product-card"><div class="product-image">${p.image?`<img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">`:''}<span class="product-image-fallback" ${p.image?'style="display:none"':''}>${p.emoji||"🧶"}</span></div><div class="product-info"><h3>${escapeHtml(p.name)}</h3><p>${escapeHtml(p.desc)}</p><div class="product-meta"><span class="price">${money(p.price)}</span><span class="stock">${p.stock} in stock</span></div><div class="card-actions"><button class="small-btn primary" onclick="openProductModal('${p.id}')">Edit</button><button class="small-btn" onclick="deleteProduct('${p.id}')">Delete</button></div></div></div>`).join("")}</div>`;
}
function renderCustomers(){
  document.getElementById("page-customers").innerHTML =
    pageTitle("Customers","Centralized customer records for easier order management.",
      `<button class="btn btn-light" onclick="exportCustomers()">Export CSV</button>`) +
    `<section class="panel table-panel">${state.customers.length?`<div class="table-wrap"><table class="data-table"><thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Total Orders</th><th>Action</th></tr></thead><tbody>${state.customers.map(c=>`<tr><td><div class="customer-cell"><div class="avatar">${c.name.split(" ").map(x=>x[0]).join("").slice(0,2)}</div><div><strong>${escapeHtml(c.name)}</strong><small>${c.id}</small></div></div></td><td>${escapeHtml(c.email)}</td><td>${escapeHtml(c.phone)}</td><td>${c.orders}</td><td><button class="small-btn primary" onclick="viewCustomer('${c.id}')">View</button></td></tr>`).join("")}</tbody></table></div>`:`<div class="empty">No customers yet.</div>`}</section>`;
}
function renderPayments(){
  const paid=state.orders.filter(o=>o.status==="Completed").reduce((a,o)=>a+o.total,0);
  const pending=state.orders.filter(o=>o.status!=="Completed"&&o.status!=="Cancelled").reduce((a,o)=>a+o.total,0);
  document.getElementById("page-payments").innerHTML =
    pageTitle("Payments","Track payment requests and transaction totals.",
      `<button class="btn btn-light" onclick="exportPayments()">Export CSV</button>`) +
    `<div class="stats">${stat("Completed Payments",money(paid),"+10.1% this month","✓","icon-green")}${stat("Pending Payments",money(pending),"Awaiting confirmation","₱","icon-orange")}${stat("COD Orders",state.orders.filter(o=>o.status!=="Cancelled").length,"Active transactions","🚚","icon-blue")}${stat("Payment Success Rate","96.4%","+2.1% this month","↗","icon-purple")}</div>
    <section class="panel table-panel"><div class="panel-head"><h3>Payment Transactions</h3><span>Connected payment methods: GCash / COD / Manual confirmation</span></div>${ordersTable(state.orders)}</section>`;
}
function renderReviews(){
  const unreadBadge = `<span class="unread-badge" id="msgUnreadBadge" style="display:none">0</span>`;
  const total = state.reviews.length;
  const avg = total ? (state.reviews.reduce((n,r)=>n+Number(r.rating||0),0)/total).toFixed(1) : "0.0";
  const distribution = [5,4,3,2,1].map(star=>({star,count:state.reviews.filter(r=>Number(r.rating)===star).length}));
  const reviewsContent = `<div class="two-col"><section class="panel"><div class="panel-head"><h3>Latest Product Reviews</h3><span>${total} review${total===1?"":"s"}</span></div><div class="panel-body">${total?state.reviews.map(r=>`<div class="review"><div class="review-head"><h4>${escapeHtml(r.customer||"Customer")}</h4><span class="stars">${"★".repeat(Number(r.rating)||0)}${"☆".repeat(5-(Number(r.rating)||0))}</span></div><strong>${escapeHtml(r.product||"Product")}</strong><p>${escapeHtml(r.text||"No written feedback.")}</p><small>${escapeHtml(r.orderCode||"")} · ${escapeHtml(formatDateTime(r.date))}</small></div>`).join(""):`<div class="empty">No product reviews have been submitted yet.</div>`}</div></section>
    <section class="panel"><div class="panel-head"><h3>Rating Summary</h3></div><div class="panel-body"><div class="donut-area" style="height:210px"><div class="donut"></div><div class="donut-center"><strong>${avg}</strong><small>out of 5 · ${total} reviews</small></div></div><div class="metric-list">${distribution.map(d=>`<div class="metric"><div class="metric-head"><span>${d.star} star${d.star===1?"":"s"}</span><strong>${total?Math.round(d.count/total*100):0}% (${d.count})</strong></div><div class="progress"><i style="width:${total?Math.round(d.count/total*100):0}%"></i></div></div>`).join("")}</div></div></section></div>`;
  document.getElementById("page-reviews").innerHTML =
    pageTitle("Customer Reviews","Verified product feedback submitted by customers.") +
    `<div class="reviews-tabs"><button class="reviews-tab-btn${reviewsActiveTab==="products"?" active":""}" data-rtab="products" onclick="switchReviewsTab('products')">Product Reviews</button><button class="reviews-tab-btn${reviewsActiveTab==="messages"?" active":""}" data-rtab="messages" onclick="switchReviewsTab('messages')">Direct Messages ${unreadBadge}</button></div>
    <div id="reviewsTab-products" class="reviews-tab-panel" style="${reviewsActiveTab==="products"?"":"display:none"}">${reviewsContent}</div>
    <div id="reviewsTab-messages" class="reviews-tab-panel" style="${reviewsActiveTab==="messages"?"":"display:none"}"><section class="panel"><div class="panel-head"><h3>Direct Messages from Customers</h3><span id="msgCountLabel"></span></div><div class="panel-body" id="messagesListMount"><div class="empty">Loading messages&hellip;</div></div></section></div>`;
  updateMsgBadge();
  const cl = document.getElementById("msgCountLabel");
  if(cl) cl.textContent = messagesCache.loaded ? `${messagesCache.list.length} message(s) &middot; ${messagesCache.unread} unread` : "";
  if(reviewsActiveTab==="messages" && currentPage==="reviews"){
    if(messagesCache.loaded) renderMessagesList(); else loadMessages();
  }
}
async function loadProductReviews(){
  try{
    const response=await fetch("../AilisCraftUser/api/product_reviews.php?action=owner-list",{headers:{"Accept":"application/json"},credentials:"same-origin",cache:"no-store"});
    const data=await response.json();
    if(!response.ok||!data.ok) throw new Error(data.error||"Unable to load product reviews.");
    state.reviews=Array.isArray(data.reviews)?data.reviews:[];
    renderDashboard(); renderReviews();
  }catch(error){
    state.reviews=[]; renderDashboard(); renderReviews();
    toast("Product reviews could not be loaded: "+error.message,"error");
  }
}

function renderReports(){
  const revenue=state.orders.reduce((a,o)=>o.status==="Cancelled"?a:a+o.total,0);
  document.getElementById("page-reports").innerHTML =
    pageTitle("Reports","Simple business reports for sales, orders, and products.",
      `<button class="btn btn-primary" onclick="exportData()">Export Full Report</button>`) +
    `<div class="report-cards"><div class="report-card"><h3>Sales Report</h3><p>Total recorded sales from active and completed orders.</p><div class="big">${money(revenue)}</div></div><div class="report-card"><h3>Order Report</h3><p>Total orders recorded in the system.</p><div class="big">${state.orders.length}</div></div><div class="report-card"><h3>Inventory Report</h3><p>Current number of products and available stock.</p><div class="big">${state.products.reduce((a,p)=>a+p.stock,0)}</div></div></div>
    <section class="panel" style="margin-top:18px"><div class="panel-head"><h3>System Objectives</h3><span>Aligned with project context</span></div><div class="panel-body"><div class="metric-list">
      <div class="metric"><div class="metric-head"><span>Centralized Order Management</span><strong>100%</strong></div><div class="progress"><i style="width:100%"></i></div></div>
      <div class="metric"><div class="metric-head"><span>Customization Information</span><strong>90%</strong></div><div class="progress"><i style="width:90%"></i></div></div>
      <div class="metric"><div class="metric-head"><span>Customer Record Accuracy</span><strong>95%</strong></div><div class="progress"><i style="width:95%"></i></div></div>
      <div class="metric"><div class="metric-head"><span>Order Status Visibility</span><strong>100%</strong></div><div class="progress"><i style="width:100%"></i></div></div>
    </div></div></section>`;
}
function renderSettings(){
  document.getElementById("page-settings").innerHTML =
    pageTitle("Settings","Configure basic system preferences.") +
    `<section class="panel"><div class="panel-head"><h3>System Preferences</h3></div><div class="panel-body">
      ${settingRow("Notifications","Show notifications for new and pending orders.","notifications")}
      ${settingRow("Automatic Backup","Keep a browser backup of dashboard data.","autoBackup")}
      ${settingRow("Guest Checkout","Allow customers to order without creating an account.","guestCheckout")}
      <div style="padding-top:18px"><button class="btn btn-danger" onclick="resetData()">Reset Demo Data</button></div>
    </div></section>`;
}
function settingRow(title,desc,key){
  return `<div class="setting-row"><div><strong>${title}</strong><small>${desc}</small></div><button class="switch ${state.settings[key]?"on":""}" onclick="toggleSetting('${key}',this)"><i></i></button></div>`;
}

function navigate(page){
  currentPage=page;
  render();
  window.scrollTo({top:0,behavior:"smooth"});
}
document.querySelectorAll(".nav-item").forEach(btn=>btn.addEventListener("click",()=>navigate(btn.dataset.page)));
document.getElementById("menuToggle").addEventListener("click",()=>document.getElementById("sidebar").classList.toggle("open"));
document.getElementById("globalSearch").addEventListener("input",()=>{
  if(currentPage==="orders") renderOrdersTable();
  else if(currentPage!=="dashboard"){
    const q=document.getElementById("globalSearch").value.toLowerCase();
    if(q) toast("Search is active. Open Orders to filter order records.","success");
  }
});
document.getElementById("notificationBtn").addEventListener("click",()=>toast(`${state.orders.filter(o=>o.status==="Preparing").length} order(s) are preparing.`));
document.getElementById("logoutBtn").addEventListener("click",()=>{ localStorage.removeItem(AUTH_KEY); window.location.replace("login.php"); });

function openModal(title,body,foot){
  document.getElementById("modal").innerHTML=`<div class="modal-head"><h3>${title}</h3><button class="modal-close" onclick="closeModal()">×</button></div><div class="modal-body">${body}</div><div class="modal-foot">${foot}</div>`;
  document.getElementById("modalBackdrop").classList.add("show");
}
function closeModal(){document.getElementById("modalBackdrop").classList.remove("show");document.getElementById("modal").classList.remove("modal-wide");}
document.getElementById("modalBackdrop").addEventListener("click",e=>{if(e.target.id==="modalBackdrop")closeModal()});

/* ----- Task 4: order snapshot lightbox + gallery helpers ----- */
function snapshotSrc(s){
  // Only real captured snapshots (data URLs / absolute / protocol-relative URLs) are trusted.
  // Cross-panel relative paths are unreliable (folders may be renamed/nested) -> reject, fall back to owner assets.
  s = String(s || "");
  if(/^(data:|https?:|\/\/)/i.test(s)) return s;
  return "";
}
function fallbackAssetSrc(name){
  const n = String(name||"").toLowerCase();
  if(/keychain|charm/.test(n)) return "assets/products/keychain1.png";
  if(/bag|sling|purse|baguette|tote/.test(n)) return "assets/products/bag1.png";
  if(/head|bandana|clip|headband|hat|ear|barrette|bow/.test(n)) return "assets/products/headwear1.png";
  if(/flower|bouquet|rose|sunflower|daisy|tulip|lavender|lily|orchid|peony|carnation|hydrangea/.test(n)) return "assets/products/flower1.png";
  return "assets/products/flower1.png";
}
function orderThumbSrc(o){
  // Never returns empty: real snapshot -> owner's own guaranteed category asset.
  const first = (o && Array.isArray(o.items) && o.items.length) ? o.items[0] : null;
  return snapshotSrc(first ? (first.snapshot || first.imageUrl) : "")
      || snapshotSrc((o && o.snapshot) || "")
      || fallbackAssetSrc((first && (first.name || first.category)) || (o && o.product) || "");
}
function unescapeNewlines(s){ return String(s||"").replace(/\\n/g,"\n").replace(/\\r/g,"\r"); }
function refPhotoSrc(p){
  if(!p) return "";
  p = String(p).trim();
  if(!p) return "";
  if(/^(data:|https?:|\/\/)/i.test(p)) return p;
  // Reference photos live in AilisCraftUser/uploads/ — cross-panel relative path is reliable for this specific folder.
  return "../AilisCraftUser/" + p.replace(/^\/+/,"");
}
function specialRequestSectionHtml(o){
  const photos = [];
  const notes = [];
  if(Array.isArray(o.items)){
    o.items.forEach(i=>{
      if(!i) return;
      if(i.specialRequestImages){
        String(i.specialRequestImages).split(",").map(s=>s.trim()).filter(Boolean).forEach(p=>photos.push({src:refPhotoSrc(p), name:i.name||"Item"}));
      }
      const sr = i.customization && i.customization.specialRequest;
      if(sr) notes.push((i.name||"Item") + ": " + sr);
    });
  }
  let html = `<div class="field full"><label>Special Request Reference Photos</label>`;
  if(notes.length) html += `<div class="sr-notes">${notes.map(n=>`<div class="sr-note">${escapeHtml(n)}</div>`).join("")}</div>`;
  if(photos.length){
    html += `<div class="snapshot-row">${photos.map(ph=>`<figure class="snapshot-item"><img class="snapshot-modal-img" src="${ph.src}" alt="${escapeHtml(ph.name)} reference photo" title="Click to enlarge" onclick="openSnapshot(this.src)" onerror="this.onerror=null;this.style.display='none'"><figcaption>${escapeHtml(ph.name)}</figcaption></figure>`).join("")}</div>`;
  } else {
    html += `<div class="cell-muted" style="padding:8px 0">No reference photos uploaded.</div>`;
  }
  html += `</div>`;
  return html;
}
function openSnapshot(src){
  if(!src) return;
  const modal = document.getElementById("modal");
  modal.classList.remove("modal-wide");
  openModal("Order Snapshot", `<img class="snapshot-large" src="${src}" alt="Order snapshot preview" onerror="this.style.display='none'">`, `<button class="btn btn-light" onclick="closeModal()">Close</button>`);
  modal.classList.add("modal-wide");
}
function snapshotGalleryHtml(o){
  if(!o) return "";
  let snaps = [];
  if(Array.isArray(o.items) && o.items.length){
    o.items.forEach(i=>{
      if(!i) return;
      const src = snapshotSrc(i.snapshot || "") || snapshotSrc(i.imageUrl || "") || snapshotSrc(o.snapshot || "") || fallbackAssetSrc(i.name || i.category || o.product || "");
      snaps.push({name:i.name||"Item", src});
    });
  }
  if(!snaps.length){
    const src = snapshotSrc(o.snapshot || "") || fallbackAssetSrc(o.product || "");
    snaps = [{name:"Order preview", src}];
  }
  if(!snaps.length) return "";
  return `<div class="field full"><label>Customization Snapshot${snaps.length>1?` (${snaps.length})`:""}</label><div class="snapshot-row">${snaps.map(s=>`<figure class="snapshot-item"><img class="snapshot-modal-img" src="${s.src}" alt="${escapeHtml(s.name)}" title="Click to enlarge" onclick="openSnapshot(this.src)" onerror="this.onerror=null;this.src='${fallbackAssetSrc(s.name)}'"><span class="snapshot-fallback" style="display:none">🧶</span><figcaption>${escapeHtml(s.name)}</figcaption></figure>`).join("")}</div></div>`;
}
function formatDateTime(s){
  if(!s) return "";
  const d = new Date(String(s).replace(" ","T"));
  if(isNaN(d.getTime())) return String(s);
  return d.toLocaleString("en-PH",{month:"short",day:"numeric",year:"numeric",hour:"numeric",minute:"2-digit"});
}

/* ----- Task 5: Direct Messages (Reviews sub-tab) ----- */
let reviewsActiveTab = "products";
const MSG_API = "api/messages.php";
const messagesCache = {loaded:false, loading:false, list:[], unread:0};
async function loadMessages(silent=false){
  if(messagesCache.loading) return;
  messagesCache.loading = true;
  try{
    const res = await fetch(MSG_API+"?action=list",{headers:{"Accept":"application/json"},cache:"no-store"});
    const data = await res.json();
    if(!res.ok || !data.ok) throw new Error(data.error||"Unable to load messages.");
    messagesCache.list = Array.isArray(data.messages)?data.messages:[];
    messagesCache.unread = messagesCache.list.filter(m=>!Number(m.is_read)).length;
    messagesCache.loaded = true;
    updateMsgBadge();
    const cl = document.getElementById("msgCountLabel");
    if(cl) cl.textContent = `${messagesCache.list.length} message(s) &middot; ${messagesCache.unread} unread`;
    if(reviewsActiveTab==="messages" && currentPage==="reviews") renderMessagesList();
  }catch(err){
    messagesCache.loaded = true;
    if(!silent) toast("Messages could not be loaded: "+err.message,"error");
    if(reviewsActiveTab==="messages"){
      const mount = document.getElementById("messagesListMount");
      if(mount) mount.innerHTML = `<div class="empty">Unable to load messages. ${escapeHtml(err.message)}</div>`;
    }
  }finally{
    messagesCache.loading = false;
  }
}
function updateMsgBadge(){
  const b = document.getElementById("msgUnreadBadge");
  if(!b) return;
  if(messagesCache.unread>0){ b.style.display="inline-flex"; b.textContent = String(messagesCache.unread); }
  else { b.style.display="none"; }
}
function switchReviewsTab(tab){
  reviewsActiveTab = tab;
  const prod = document.getElementById("reviewsTab-products");
  const msg = document.getElementById("reviewsTab-messages");
  document.querySelectorAll(".reviews-tab-btn").forEach(b=>b.classList.toggle("active", b.dataset.rtab===tab));
  if(prod) prod.style.display = (tab==="products")?"":"none";
  if(msg) msg.style.display = (tab==="messages")?"":"none";
  if(tab==="messages"){
    if(!messagesCache.loaded) loadMessages();
    else renderMessagesList();
  }
}
function renderMessagesList(){
  const mount = document.getElementById("messagesListMount");
  if(!mount) return;
  if(!messagesCache.list.length){ mount.innerHTML = `<div class="empty">No direct messages yet.</div>`; return; }
  mount.innerHTML = `<div class="msg-list">${messagesCache.list.map(m=>{
    const unread = !Number(m.is_read);
    return `<div class="msg-card${unread?" unread":""}" data-msg-id="${Number(m.id)}">
      <div class="msg-head">
        <div class="msg-sender"><strong>${escapeHtml(m.name||"Customer")}</strong> <span class="msg-email">${escapeHtml(m.email||"")}</span></div>
        <span class="msg-date">${escapeHtml(formatDateTime(m.created_at))}</span>
      </div>
      <div class="msg-subject">${escapeHtml(m.subject||"(no subject)")}</div>
      <div class="msg-body">${escapeHtml(m.message||"").replace(/\r?\n/g,"<br>")}</div>
      <div class="msg-actions">${unread?`<button class="small-btn primary" onclick="markMessageRead(${Number(m.id)},this)">Mark as read</button>`:`<span class="msg-read-label">Read</span>`}</div>
    </div>`;
  }).join("")}</div>`;
}
async function markMessageRead(id, btn){
  if(btn){ btn.disabled = true; btn.textContent = "Saving&hellip;"; }
  try{
    const res = await fetch(MSG_API+"?action=mark-read",{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json"},body:JSON.stringify({id:Number(id)}),cache:"no-store"});
    const data = await res.json();
    if(!res.ok || !data.ok) throw new Error(data.error||"Could not mark as read.");
    const m = messagesCache.list.find(x=>Number(x.id)===Number(id));
    if(m) m.is_read = 1;
    messagesCache.unread = Math.max(0, messagesCache.unread-1);
    updateMsgBadge();
    const cl = document.getElementById("msgCountLabel");
    if(cl) cl.textContent = `${messagesCache.list.length} message(s) &middot; ${messagesCache.unread} unread`;
    const card = document.querySelector('.msg-card[data-msg-id="'+Number(id)+'"]');
    if(card){
      card.classList.remove("unread");
      const actions = card.querySelector(".msg-actions");
      if(actions) actions.innerHTML = `<span class="msg-read-label">Read</span>`;
    }
    toast("Message marked as read.");
  }catch(err){
    if(btn){ btn.disabled = false; btn.textContent = "Mark as read"; }
    toast(err.message,"error");
  }
}

function openProductModal(id){
  const p=id?state.products.find(x=>x.id===id):null;
  openModal(id?"Edit Product":"Add Product",`<form id="productForm" class="form-grid">
    <div class="field full"><label>Product Name</label><input name="name" required value="${escapeHtml(p?.name||"")}"></div>
    <div class="field"><label>Category</label><select name="category"><option>Flowers</option><option>Bags</option><option>Head Wears</option><option>Keychains</option></select></div>
    <div class="field"><label>Price (₱)</label><input name="price" type="number" min="0" required value="${p?.price||""}"></div>
    <div class="field"><label>Stock</label><input name="stock" type="number" min="0" required value="${p?.stock??""}"></div>
    <div class="field"><label>Emoji / Icon</label><input name="emoji" maxlength="4" value="${escapeHtml(p?.emoji||"🧶")}"></div>
    <div class="field full"><label>Product Image Path</label><input name="image" value="${escapeHtml(p?.image||"")}" placeholder="assets/products/example.png"></div>
    <div class="field full"><label>Description</label><textarea name="desc">${escapeHtml(p?.desc||"")}</textarea></div>
  </form>`,`<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="saveProduct('${id||""}')">Save Product</button>`);
  if(p) document.querySelector('#productForm select[name="category"]').value=p.category;
}
function saveProduct(id){
  const f=document.getElementById("productForm");
  if(!f.reportValidity())return;
  const d=Object.fromEntries(new FormData(f).entries());
  d.price=Number(d.price);d.stock=Number(d.stock);
  if(id){Object.assign(state.products.find(p=>p.id===id),d);toast("Product updated.");}
  else{d.id="P"+String(Date.now()).slice(-5);state.products.unshift(d);toast("Product added.");}
  saveState();closeModal();render();
}
function deleteProduct(id){
  if(!confirm("Delete this product?"))return;
  state.products=state.products.filter(p=>p.id!==id);saveState();render();toast("Product deleted.","error");
}

function openOrderModal(){
  const productOptions=state.products.map(p=>`<option value="${escapeHtml(p.name)}" data-price="${p.price}">${escapeHtml(p.name)} — ${money(p.price)}</option>`).join("");
  const customerOptions=state.customers.map(c=>`<option>${escapeHtml(c.name)}</option>`).join("");
  openModal("Create New Order",`<form id="orderForm" class="form-grid">
    <div class="field"><label>Customer</label><select name="customer">${customerOptions}</select></div>
    <div class="field"><label>Product</label><select id="orderProduct" name="product" onchange="updateOrderTotal()">${productOptions}</select></div>
    <div class="field"><label>Quantity</label><input id="orderQty" name="qty" type="number" min="1" value="1" onchange="updateOrderTotal()"></div>
    <div class="field"><label>Total</label><input id="orderTotal" name="total" readonly value="0"></div>
    <div class="field full"><label>Customization Details</label><textarea name="customization" placeholder="Example: Pink flowers, white ribbon, initials..."></textarea></div>
    <div class="field"><label>Status</label><select name="status"><option>Pending</option><option>Processing</option><option>Ready</option><option>Completed</option></select></div>
    <div class="field"><label>Order Date</label><input name="date" type="date" value="${new Date().toISOString().slice(0,10)}"></div>
  </form>`,`<button class="btn btn-light" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="saveOrder()">Create Order</button>`);
  updateOrderTotal();
}
function updateOrderTotal(){
  const sel=document.getElementById("orderProduct");if(!sel)return;
  const price=Number(sel.selectedOptions[0]?.dataset.price||0),qty=Number(document.getElementById("orderQty").value||1);
  document.getElementById("orderTotal").value=money(price*qty);
}
function saveOrder(){
  const f=document.getElementById("orderForm");if(!f.reportValidity())return;
  const d=Object.fromEntries(new FormData(f).entries());
  d.id="ORD-"+String(Date.now()).slice(-5);d.qty=Number(d.qty);d.total=Number(d.total.replace(/[₱,]/g,""));
  state.orders.unshift(d);
  const c=state.customers.find(x=>x.name===d.customer);if(c)c.orders++;
  saveState();closeModal();render();toast("New order created successfully.");
}
function viewOrder(id){
  const o=state.orders.find(x=>x.id===id);if(!o)return;
  const statuses=["Preparing","Shipped","Out for Delivery","Delivered"];
  openModal("Order "+o.id,`<div class="form-grid">
    <div class="field"><label>Customer</label><input readonly value="${escapeHtml(o.customer)}"></div>
    <div class="field"><label>Total</label><input readonly value="${money(o.total)}"></div>
    <div class="field full"><label>Product</label><input readonly value="${escapeHtml(o.product)} × ${o.qty}"></div>
    <div class="field full"><label>Customization / Special Requests / Letter</label><textarea readonly>${escapeHtml(unescapeNewlines(o.customization || "No customization details saved."))}</textarea></div>
    ${snapshotGalleryHtml(o)}
    ${specialRequestSectionHtml(o)}
    <div class="field full"><label>Delivery / Payment Details</label><textarea readonly>${escapeHtml(unescapeNewlines([o.address ? "Address: "+o.address : "",o.contact ? "Contact: "+o.contact : "",o.deliveryDate ? "Delivery date: "+o.deliveryDate : "",o.paymentMethod ? "Payment: "+o.paymentMethod : "",o.paymentStatus ? "Payment status: "+o.paymentStatus : ""].filter(Boolean).join("\n")))}</textarea></div>
    <div class="field"><label>Order Status</label><select id="viewStatus">${statuses.map(s=>`<option ${s===o.status?"selected":""}>${s}</option>`).join("")}</select></div>
    <div class="field"><label>Date</label><input readonly value="${escapeHtml(o.date)}"></div>
  </div>`,`<button class="btn btn-light" onclick="closeModal()">Close</button><button class="btn btn-primary" onclick="updateOrderStatus('${o.id}')">Save Status</button>`);
}
async function updateOrderStatus(id){
  const o=state.orders.find(x=>x.id===id);if(!o)return;
  const status=document.getElementById("viewStatus")?.value;
  if(!["Preparing","Shipped","Out for Delivery","Delivered"].includes(status))return;
  try {
    const response = await fetch(ORDER_API + "?action=owner-status", {
      method:"POST", headers:{"Content-Type":"application/json","Accept":"application/json"},
      body:JSON.stringify({orderCode:o.id,status}), cache:"no-store"
    });
    const data = await response.json();
    if(!response.ok || !data.ok) throw new Error(data.error || "Status could not be saved.");
    closeModal(); await syncOrdersFromDatabase();
    toast("Order status saved to the database and synchronized.");
  } catch(error) { toast("Status was not saved: " + error.message, "error"); }
}
function viewCustomer(id){
  const c=state.customers.find(x=>x.id===id);const orders=state.orders.filter(o=>o.customer===c.name);
  openModal(c.name,`<div class="form-grid"><div class="field"><label>Email</label><input readonly value="${escapeHtml(c.email)}"></div><div class="field"><label>Phone</label><input readonly value="${escapeHtml(c.phone)}"></div></div><h4 style="font-size:12px;margin:22px 0 8px">Order History</h4>${ordersTable(orders)}`,`<button class="btn btn-light" onclick="closeModal()">Close</button>`);
}
function toggleSetting(key,el){state.settings[key]=!state.settings[key];saveState();renderSettings();toast("Setting updated.");}
function resetData(){if(!confirm("Reset all demo data?"))return;state=JSON.parse(JSON.stringify(defaultState));saveState();render();toast("Demo data reset.","success")}
function csv(rows){
  return rows.map(r=>r.map(v=>`"${String(v??"").replace(/"/g,'""')}"`).join(",")).join("\n");
}
function download(name,content,type="text/csv"){
  const a=document.createElement("a");a.href=URL.createObjectURL(new Blob([content],{type}));a.download=name;a.click();setTimeout(()=>URL.revokeObjectURL(a.href),1000);
}
function exportOrders(){download("ailis-craft-orders.csv",csv([["Order ID","Customer","Product","Qty","Total","Status","Date"],...state.orders.map(o=>[o.id,o.customer,o.product,o.qty,o.total,o.status,o.date])]));toast("Orders exported.")}
function exportCustomers(){download("ailis-craft-customers.csv",csv([["ID","Name","Email","Phone","Orders"],...state.customers.map(c=>[c.id,c.name,c.email,c.phone,c.orders])]));toast("Customers exported.")}
function exportPayments(){exportOrders()}
function exportData(){download("ailis-craft-dashboard-backup.json",JSON.stringify(state,null,2),"application/json");toast("Full dashboard backup exported.")}

render();
syncOrdersFromDatabase();
loadMessages(true);
loadProductReviews();
