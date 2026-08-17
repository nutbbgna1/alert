const STORAGE_KEY = "alert_green_demo_v2";
const defaultState = {
  alerts: [
    {id:1,title:"ส่งรายงานประจำสัปดาห์",date:"2026-08-17",time:"09:00",category:"work",priority:"high",status:"active",description:"สรุปผลการทำงานประจำสัปดาห์"},
    {id:2,title:"ประชุมทีม Project Alpha",date:"2026-08-17",time:"13:30",category:"work",priority:"medium",status:"active",description:"ประชุมอัปเดตงานกับทีม"},
    {id:3,title:"จ่ายค่าอินเทอร์เน็ต",date:"2026-08-17",time:"18:00",category:"finance",priority:"medium",status:"active",description:"ชำระก่อนครบกำหนด"},
    {id:4,title:"อ่านหนังสือ 30 นาที",date:"2026-08-17",time:"20:30",category:"study",priority:"low",status:"active",description:"ทบทวนบทเรียน"},
    {id:5,title:"นัดพบลูกค้า KBank",date:"2026-08-18",time:"10:00",category:"work",priority:"medium",status:"active",description:"ประชุมแผนงานรอบใหม่"},
    {id:6,title:"อัปเดตการใช้งานระบบใหม่",date:"2026-08-19",time:"14:00",category:"work",priority:"medium",status:"active",description:"ตรวจรายการที่ต้องแก้"},
    {id:7,title:"ตรวจสุขภาพประจำปี",date:"2026-08-21",time:"09:00",category:"personal",priority:"low",status:"active",description:""},
    {id:8,title:"สรุปค่าใช้จ่ายประจำเดือน",date:"2026-08-24",time:"16:00",category:"finance",priority:"medium",status:"active",description:""}
  ],
  notes: [
    {id:1,title:"ไอเดียโปรเจกต์ใหม่",body:"ระบบจัดการงานที่จดง่ายและตั้งเตือนได้ในหน้าจอเดียว",date:"20 Aug"},
    {id:2,title:"รหัส Wi-Fi บ้าน",body:"Network: Home_5G\nPassword: example1234",date:"18 Aug"},
    {id:3,title:"รายการซื้อของ",body:"Apples\nGreek yogurt\nGranola",date:"15 Aug"}
  ],
  settings:{email:true,browser:false,calendarConnected:false,defaultReminder:"1 hour before"}
};
let state = JSON.parse(localStorage.getItem(STORAGE_KEY) || "null") || defaultState;
let currentView="dashboard";
let selectedDate="2026-08-17";
let calendarMonth=7, calendarYear=2026;
let calendarView="month";

const qs=(s)=>document.querySelector(s);
const qsa=(s)=>[...document.querySelectorAll(s)];
function save(){localStorage.setItem(STORAGE_KEY,JSON.stringify(state))}
function catName(c){return ({work:"Work",study:"Study",personal:"Personal",finance:"Finance",health:"Health"})[c]||c}
function priorityLabel(p){return p.charAt(0).toUpperCase()+p.slice(1)}
function fmtDate(dateStr){return new Date(dateStr+"T00:00:00").toLocaleDateString("en-GB",{day:"numeric",month:"short",year:"numeric"})}
function toast(msg){const el=qs("#toast");el.textContent=msg;el.classList.add("show");setTimeout(()=>el.classList.remove("show"),2200)}

function switchView(view){
  currentView=view;
  qsa(".view").forEach(v=>v.classList.toggle("active",v.id===`view-${view}`));
  qsa("[data-view]").forEach(b=>b.classList.toggle("active",b.dataset.view===view));
  if(view==="calendar") renderFullCalendar();
  if(view==="alerts") renderAlertsTable();
  if(view==="notes") renderNotes();
  window.scrollTo({top:0,behavior:"smooth"});
}
qsa("[data-view]").forEach(b=>b.addEventListener("click",()=>switchView(b.dataset.view)));
qsa("[data-view-jump]").forEach(b=>b.addEventListener("click",()=>switchView(b.dataset.viewJump)));

function taskRow(a){
  return `<div class="task-row">
    <button class="check ${a.status==="completed"?"completed":""}" data-complete="${a.id}" title="Toggle complete"></button>
    <div class="task-time">${a.time}</div>
    <div><div class="task-title">${escapeHtml(a.title)}</div><div class="task-meta">${catName(a.category)}</div></div>
    <div class="priority ${a.priority}">${priorityLabel(a.priority)}</div>
  </div>`;
}
function renderDashboard(){
  const today=state.alerts.filter(a=>a.date==="2026-08-17");
  qs("#todayList").innerHTML=today.map(taskRow).join("") || emptyState("No alerts today");
  qs("#todayFullList").innerHTML=today.map(taskRow).join("") || emptyState("No alerts today");
  const upcoming=state.alerts.filter(a=>a.date>"2026-08-17"&&a.status!=="completed").sort((a,b)=>(a.date+a.time).localeCompare(b.date+b.time)).slice(0,4);
  qs("#upcomingList").innerHTML=upcoming.map(a=>`<div class="upcoming-row">
    <div class="date-block"><strong>${fmtDate(a.date).split(" ").slice(0,2).join(" ")}</strong><span>${new Date(a.date+"T00:00:00").toLocaleDateString("en-GB",{weekday:"short"})}</span></div>
    <strong>${a.time}</strong><div>${escapeHtml(a.title)}<div class="task-meta">${catName(a.category)}</div></div>
    <div class="priority ${a.priority}">${priorityLabel(a.priority)}</div></div>`).join("")||emptyState("No upcoming alerts");
  qs("#notesPreview").innerHTML=state.notes.slice(0,3).map(n=>`<div class="note-line"><strong>${escapeHtml(n.title)}</strong><span>${escapeHtml(n.body).replace(/\n/g," · ")}</span></div>`).join("");
  renderMiniCalendar();
  renderStats();
  bindCompleteButtons();
}
function renderStats(){
  const todayActive=state.alerts.filter(a=>a.date==="2026-08-17"&&a.status!=="completed").length;
  qs("#statToday").textContent=todayActive;
  qs("#statUpcoming").textContent=state.alerts.filter(a=>a.date>"2026-08-17"&&a.status!=="completed").length;
  qs("#statCompleted").textContent=state.alerts.filter(a=>a.status==="completed").length+18;
}
function miniCalendarHtml(year,month){
  const days=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  const first=new Date(year,month,1).getDay(), total=new Date(year,month+1,0).getDate(), prevTotal=new Date(year,month,0).getDate();
  let html=days.map(d=>`<div class="cal-head">${d}</div>`).join("");
  for(let i=first-1;i>=0;i--) html+=`<div class="cal-day muted">${prevTotal-i}</div>`;
  for(let d=1;d<=total;d++){
    const ds=`${year}-${String(month+1).padStart(2,"0")}-${String(d).padStart(2,"0")}`;
    const has=state.alerts.some(a=>a.date===ds);
    html+=`<div class="cal-day ${ds==="2026-08-17"?"today":""} ${has?"has-event":""}" data-date="${ds}">${d}</div>`;
  }
  return html;
}
function renderMiniCalendar(){
  qs("#miniCalendar").innerHTML=miniCalendarHtml(2026,7);
  const list=state.alerts.filter(a=>a.date==="2026-08-17").slice(0,4);
  qs("#calendarAgenda").innerHTML=list.map(a=>`<div class="agenda-line"><span>${a.time}</span><div>${escapeHtml(a.title)}</div></div>`).join("");
}
function renderFullCalendar(){
  if(calendarView === "week") return renderWeekCalendar();
  if(calendarView === "day") return renderDayCalendar();

  const title=new Date(calendarYear,calendarMonth,1).toLocaleDateString("en-GB",{month:"long",year:"numeric"});
  qs("#calendarTitle").textContent=title;
  const days=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  const first=new Date(calendarYear,calendarMonth,1).getDay(), total=new Date(calendarYear,calendarMonth+1,0).getDate(), prevTotal=new Date(calendarYear,calendarMonth,0).getDate();
  let html=days.map(d=>`<div class="cal-head">${d}</div>`).join("");
  for(let i=first-1;i>=0;i--) html+=`<div class="cal-day muted">${prevTotal-i}</div>`;
  for(let d=1;d<=total;d++){
    const ds=`${calendarYear}-${String(calendarMonth+1).padStart(2,"0")}-${String(d).padStart(2,"0")}`;
    const events=state.alerts.filter(a=>a.date===ds);
    html+=`<div class="cal-day ${ds==="2026-08-17"?"today":""}" data-date="${ds}"><strong>${d}</strong>${events.slice(0,2).map(e=>`<span class="event-chip">${escapeHtml(e.title)}</span>`).join("")}</div>`;
  }
  qs("#fullCalendar").innerHTML=html;
  qsa("#fullCalendar [data-date]").forEach(el=>el.addEventListener("click",()=>{selectedDate=el.dataset.date;renderSelectedDay()}));
  renderSelectedDay();
}
function renderSelectedDay(){
  qs("#selectedDayLabel").textContent=fmtDate(selectedDate);
  const list=state.alerts.filter(a=>a.date===selectedDate);
  qs("#selectedDayAgenda").innerHTML=list.map(taskRow).join("")||emptyState("No alerts on this day");
  bindCompleteButtons();
}

qsa("#view-calendar .toggle-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    qsa("#view-calendar .toggle-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    calendarView = btn.textContent.toLowerCase();
    renderFullCalendar();
  });
});

function renderWeekCalendar() {
  const d = new Date(selectedDate || "2026-08-17");
  const firstDay = d.getDay();
  const startOfWeek = new Date(d);
  startOfWeek.setDate(d.getDate() - firstDay);
  
  const endOfWeek = new Date(startOfWeek);
  endOfWeek.setDate(startOfWeek.getDate() + 6);
  
  qs("#calendarTitle").textContent = startOfWeek.toLocaleDateString("en-GB",{month:"short",day:"numeric"}) + " - " + endOfWeek.toLocaleDateString("en-GB",{month:"short",day:"numeric",year:"numeric"});
  
  const days=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  let html = days.map(dayName => `<div class="cal-head">${dayName}</div>`).join("");
  
  for(let i=0; i<7; i++) {
    const cur = new Date(startOfWeek);
    cur.setDate(startOfWeek.getDate() + i);
    const ds = `${cur.getFullYear()}-${String(cur.getMonth()+1).padStart(2,"0")}-${String(cur.getDate()).padStart(2,"0")}`;
    const events = state.alerts.filter(a => a.date === ds).sort((a,b)=>a.time.localeCompare(b.time));
    
    html += `<div class="cal-day ${ds==="2026-08-17"?"today":""}" data-date="${ds}" style="min-height: 120px;">
      <strong>${cur.getDate()}</strong>
      <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 4px;">
        ${events.map(e => `<span class="event-chip" style="white-space: normal; height: auto;">${e.time} ${escapeHtml(e.title)}</span>`).join("")}
      </div>
    </div>`;
  }
  qs("#fullCalendar").innerHTML = html;
  qsa("#fullCalendar [data-date]").forEach(el=>el.addEventListener("click",()=>{selectedDate=el.dataset.date;renderFullCalendar()}));
  renderSelectedDay();
}

function renderDayCalendar() {
  const cur = new Date(selectedDate || "2026-08-17");
  qs("#calendarTitle").textContent = cur.toLocaleDateString("en-GB",{weekday:"long", month:"long", day:"numeric", year:"numeric"});
  
  const ds = `${cur.getFullYear()}-${String(cur.getMonth()+1).padStart(2,"0")}-${String(cur.getDate()).padStart(2,"0")}`;
  const events = state.alerts.filter(a => a.date === ds).sort((a,b)=>a.time.localeCompare(b.time));
  
  let html = `<div style="grid-column: 1 / -1; padding: 20px;">`;
  if(events.length === 0) {
    html += emptyState("No alerts for this day");
  } else {
    html += `<div style="display: flex; flex-direction: column; gap: 12px;">` + events.map(e => `<div class="task-row">
      <div class="task-time">${e.time}</div>
      <div><div class="task-title">${escapeHtml(e.title)}</div><div class="task-meta">${catName(e.category)}</div></div>
      <div class="priority ${e.priority}">${priorityLabel(e.priority)}</div>
    </div>`).join("") + `</div>`;
  }
  html += `</div>`;
  
  qs("#fullCalendar").innerHTML = html;
  renderSelectedDay();
}
qs("#prevMonth").addEventListener("click",()=>{
  if (calendarView === "month") {
    calendarMonth--;if(calendarMonth<0){calendarMonth=11;calendarYear--}
  } else if (calendarView === "week") {
    const d = new Date(selectedDate); d.setDate(d.getDate() - 7);
    selectedDate = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
  } else {
    const d = new Date(selectedDate); d.setDate(d.getDate() - 1);
    selectedDate = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
  }
  renderFullCalendar();
});
qs("#nextMonth").addEventListener("click",()=>{
  if (calendarView === "month") {
    calendarMonth++;if(calendarMonth>11){calendarMonth=0;calendarYear++}
  } else if (calendarView === "week") {
    const d = new Date(selectedDate); d.setDate(d.getDate() + 7);
    selectedDate = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
  } else {
    const d = new Date(selectedDate); d.setDate(d.getDate() + 1);
    selectedDate = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
  }
  renderFullCalendar();
});

function renderAlertsTable(){
  const search=qs("#alertSearch")?.value.toLowerCase()||"";
  const status=qs("#statusFilter")?.value||"all";
  const rows=state.alerts.filter(a=>(!search||a.title.toLowerCase().includes(search))&&(status==="all"||a.status===status));
  qs("#alertsTable").innerHTML=rows.map(a=>`<tr>
    <td><strong>${escapeHtml(a.title)}</strong><div class="task-meta">${escapeHtml(a.description||"")}</div></td>
    <td>${fmtDate(a.date)}<br><span class="task-meta">${a.time}</span></td>
    <td>${catName(a.category)}</td>
    <td><span class="priority ${a.priority}">${priorityLabel(a.priority)}</span></td>
    <td><span class="status ${a.status}">${a.status==="completed"?"Completed":"Active"}</span></td>
    <td><button class="text-btn" data-delete="${a.id}">Delete</button></td></tr>`).join("")||`<tr><td colspan="6">No alerts found.</td></tr>`;
  qsa("[data-delete]").forEach(b=>b.addEventListener("click",()=>{state.alerts=state.alerts.filter(a=>a.id!=b.dataset.delete);save();renderAll();toast("Alert deleted")}));
}
qs("#alertSearch").addEventListener("input",renderAlertsTable);
qs("#statusFilter").addEventListener("change",renderAlertsTable);

function renderNotes(){
  qs("#notesGrid").innerHTML=state.notes.map(n=>`<article class="note-card"><small>${n.date}</small><h3>${escapeHtml(n.title)}</h3><p>${escapeHtml(n.body).replace(/\n/g,"<br>")}</p><button class="text-btn" data-note-delete="${n.id}">Delete</button></article>`).join("");
  qsa("[data-note-delete]").forEach(b=>b.addEventListener("click",()=>{state.notes=state.notes.filter(n=>n.id!=b.dataset.noteDelete);save();renderAll();toast("Note deleted")}));
}
qs("#addNoteBtn").addEventListener("click",()=>{
  const title=prompt("Note title");
  if(!title)return;
  const body=prompt("Note details")||"";
  state.notes.unshift({id:Date.now(),title,body,date:"17 Aug"});
  save();renderAll();toast("Note added");
});
function renderCategories(){
  const cats=["work","study","personal","finance","health"];
  qs("#categoriesGrid").innerHTML=cats.map(c=>{const count=state.alerts.filter(a=>a.category===c).length;return `<article class="category-card"><div class="big-dot"></div><h3>${catName(c)}</h3><strong>${count}</strong><span class="subtle">alerts</span></article>`}).join("");
}
function bindCompleteButtons(){
  qsa("[data-complete]").forEach(b=>b.onclick=()=>{const a=state.alerts.find(x=>x.id==b.dataset.complete);if(a){a.status=a.status==="completed"?"active":"completed";save();renderAll();toast(a.status==="completed"?"Marked as completed":"Marked as active")}});
}
function emptyState(text){return `<div style="padding:26px 8px;color:#7b877f">${text}</div>`}
function escapeHtml(str=""){return String(str).replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]))}

const backdrop=qs("#modalBackdrop");
function openModal(){backdrop.classList.add("open");qs("#alertTitle").focus()}
function closeModal(){backdrop.classList.remove("open");qs("#alertForm").reset();setDefaultDateTime()}
qsa("[data-open-modal]").forEach(b=>b.addEventListener("click",openModal));
qs("#closeModal").addEventListener("click",closeModal);qs("#cancelModal").addEventListener("click",closeModal);
backdrop.addEventListener("click",e=>{if(e.target===backdrop)closeModal()});
function setDefaultDateTime(){qs("#alertDate").value="2026-08-17";qs("#alertTime").value="09:00";qs("#alertEmail").checked=true}
setDefaultDateTime();
qs("#alertForm").addEventListener("submit",e=>{
  e.preventDefault();
  const alert={id:Date.now(),title:qs("#alertTitle").value.trim(),date:qs("#alertDate").value,time:qs("#alertTime").value,category:qs("#alertCategory").value,priority:qs("#alertPriority").value,status:"active",description:qs("#alertDescription").value.trim(),email:qs("#alertEmail").checked,calendar:qs("#alertCalendar").checked};
  state.alerts.push(alert);save();closeModal();renderAll();toast("Alert created");
});

qsa("[data-filter]").forEach(btn=>btn.addEventListener("click",()=>{
  qsa("[data-filter]").forEach(b=>b.classList.remove("active"));btn.classList.add("active");
  const f=btn.dataset.filter, list=state.alerts.filter(a=>a.date==="2026-08-17"&&(f==="all"||a.category===f));
  qs("#todayFullList").innerHTML=list.map(taskRow).join("")||emptyState("No alerts in this category");bindCompleteButtons();
}));
qs("#globalSearch").addEventListener("input",e=>{
  const q=e.target.value.toLowerCase().trim();
  if(q.length<2)return;
  const found=state.alerts.find(a=>a.title.toLowerCase().includes(q));
  if(found){switchView("alerts");qs("#alertSearch").value=q;renderAlertsTable()}
});
qs("#connectCalendarBtn").addEventListener("click",()=>{
  state.settings.calendarConnected=!state.settings.calendarConnected;
  save();renderSettings();toast(state.settings.calendarConnected?"Google Calendar connected in demo mode":"Google Calendar disconnected");
});
qs("#emailToggle").addEventListener("change",e=>{state.settings.email=e.target.checked;save()});
qs("#browserToggle").addEventListener("change",async e=>{
  state.settings.browser=e.target.checked;save();
  if(e.target.checked && "Notification" in window){try{await Notification.requestPermission()}catch{}}
});
qs("#defaultReminder").addEventListener("change",e=>{state.settings.defaultReminder=e.target.value;save()});
function renderSettings(){
  qs("#emailToggle").checked=state.settings.email;
  qs("#browserToggle").checked=state.settings.browser;
  qs("#defaultReminder").value=state.settings.defaultReminder;
  qs("#calendarStatus").textContent=state.settings.calendarConnected?"Connected in demo mode":"Not connected";
  qs("#connectCalendarBtn").textContent=state.settings.calendarConnected?"Disconnect":"Connect";
}
function renderAll(){renderDashboard();renderAlertsTable();renderNotes();renderCategories();renderSettings();if(currentView==="calendar")renderFullCalendar()}
renderAll();
