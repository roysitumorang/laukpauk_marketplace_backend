/* Add here all your JS customizations */
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}

function countlimit(maxlength,e,placeholder){
	var theform=eval(placeholder)
	var lengthleft=maxlength-theform.value.length
	var placeholderobj=document.all? document.all[placeholder] : document.getElementById(placeholder)
		if (window.event||e.target&&e.target==eval(placeholder)){
			if (lengthleft<0)
				theform.value=theform.value.substring(0,maxlength)
			placeholderobj.innerHTML=lengthleft
		}
}

function displaylimit(thename, theid, thelimit){
	var theform=theid!=""? document.getElementById(theid) : thename
	var limit_text='Tinggal <b><span id="'+theform.toString()+'">'+thelimit+'</span></b> karakter lagi'
	if (document.all||ns6)
		document.write(limit_text)
	if (document.all){
		eval(theform).onkeypress=function(){ return restrictinput(thelimit,event,theform)}
		eval(theform).onkeyup=function(){ countlimit(thelimit,event,theform)}
	}
	else if (ns6){
		document.body.addEventListener('keypress', function(event) { restrictinput(thelimit,event,theform) }, true); 
		document.body.addEventListener('keyup', function(event) { countlimit(thelimit,event,theform) }, true); 
	}
}

function validasi(text, alamat) {
   if (confirm(text, alamat)) {
      window.location = eval("'"+alamat+"'");
   }
}

var newwindow;
function open_window(url)
{
	newwindow = window.open(url,'popup','height=800,width=800,resizeable=no,scrollbars=yes,toolbar=no,status=no');
	if (window.focus) { newwindow.focus()	}
}

function validasi(text, alamat) {
   if (confirm(text, alamat)) {
      window.location = eval("'"+alamat+"'");
   }
}

var newwindow;
function open_window(url, myscroll)
{
	if (myscroll=='')
		myscroll = 'no';
		
	newwindow = window.open(url,'popup','height=800,width=800,resizeable=no,scrollbars='+myscroll+',toolbar=no,status=no');
	if (window.focus) { newwindow.focus()	}
}

function tampilKan(idComp)
{
	if (document.getElementById(idComp).style.display=="block")
		document.getElementById(idComp).style.display = "none";	
	else
		document.getElementById(idComp).style.display = "block";	
}

function pickSelect(name, idComp, total)
{
	for (i=0;i<total;i++)
	{
		if (i !== idComp)
			document.getElementById(name+i).style.display = "none";
	}
	document.getElementById(name+idComp).style.display="block";
}

checked = false;
function checkedAll (name) {
	if (checked == false){checked = true}else{checked = false}
	for (var i = 0; i < document.getElementById(name).elements.length; i++) {
		document.getElementById(name).elements[i].checked = checked;
	}
}
