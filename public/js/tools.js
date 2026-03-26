

function abreviaturaMes(mes){
    const meses = ["ENR","FEBR","MZO","ABR","MYO","JUN","JUL","AGTO","SPT","OCT","NOV","DIC"]

    return meses[mes];

}
function  abreviaturaMesIngles(mes){
    const meses = ["JAN","FEB","MAR","APR","MAY","JUNE","JULY","AUG","SEPT","OCT","NOV","DEC"];

    return meses[mes];
}
function getdiasSemanas(idioma){
    if (idioma=='es'){
        return ['Dom', 'Lun', 'Mar', 'Mir', 'Jue', 'Vie', 'Sab']
    }else{
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fir', 'Sat']
    }

}
function formatFechaVisual(fecha){
    let d = new Date(fecha)
    return abreviaturaMes(d.getMonth())+" "+ (d.getDate()+1)  + " del," + d.getFullYear()
}
function getMesAbreLinst(idioma) {

    if (idioma=='es'){
        return ["En","Febr","Mzo","Abr","My","Jun","Jul","Agto","Spt","Oct","Nov","Dic"];
    }else {
        return   ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    }
}
function renombrarURL(url,titulo='',data=null) {
    window.history.pushState(data, titulo, url);
}
function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}
function getPathURL() {
    return location.href.slice(_URL.length);
}
function _ajaxDOM(url,contenedor_id) {
    $.ajax({
        headers: {
            'token-app':localStorage.getItem("_token"),
        },
        type: 'POST',
        url: _URL+url,
        success: function (resp) {
            $("#loader-menor").hide()
            $("#"+contenedor_id).html(resp);
        }
    });

}

function alertError(title,msg){

    return Swal.fire({icon: 'error',
        title: title,
        text: msg,})
}
function alertExito(title,msg){
    return Swal.fire({icon: 'success',
        title: title,
        text: msg,})
}
function alertAdvertencia(title,msg){
    return Swal.fire({icon: 'warning',
        title: title,
        text: msg,})
}
function alertInfo(title,msg){
    return Swal.fire({icon: 'info',
        title: title,
        text: msg,})
}
function getTime( ) {
    var d = new Date( );
    d.setHours( d.getHours() ); // offset from local time
    var h = (d.getHours() % 12) || 12; // show midnight & noon as 12
    return (
        ( h < 10 ? '0' : '') + h +
        ( d.getMinutes() < 10 ? ':0' : ':') + d.getMinutes() +
        // optional seconds display
        // ( d.getSeconds() < 10 ? ':0' : ':') + d.getSeconds() +
        ( d.getHours() < 12 ? ' AM' : ' PM' )
    );

}

function _ajax(url,method,data={},func) {
    $.ajax({
        type: method,
        url: _URL+url,
        headers: {
            'token-app':localStorage.getItem("_token"),
        },
        data: data,
        success: function (resp) {
            $("#loader-menor").hide()
            if (isJson(resp)){
                func(JSON.parse(resp));
            }else{
                console.log(resp)
                alertError('ERR','Error en el servidor')
            }

        }
    });

}
function downloadFile(filePath){
    var link=document.createElement('a');
    link.href = filePath;
    link.download = filePath.substr(filePath.lastIndexOf('/') + 1);
    link.click();
}

function _post(url,data={},func) {
    _ajax(url,"POST",data,func)
}
function _get(url,func) {
    _ajax(url,"GET",{},func)
}

function reloadPagDon() {

}