var temp ;
$(document).ready(function () {
    $(".menu-link").click(function (evt) {
        evt.preventDefault();
        $("#loader-menor").show()
        const url_ter = $( evt.currentTarget).attr('href');
        const titulo  = $( evt.currentTarget).attr('title');
         console.log(url_ter,titulo);
        renombrarURL(_URL+url_ter,titulo);
        _ajaxDOM(url_ter,'contenedor-app')

    })
    $("#contenedor-app").on("click",".button-link",function(evt){
        evt.preventDefault();
        //$("#loader-menor").show()
        const url_ter = $( evt.currentTarget).attr('href');
        const titulo  = $( evt.currentTarget).attr('title');
        renombrarURL(_URL+url_ter,titulo);
        _ajaxDOM(url_ter,'contenedor-app')
    })
})

function reselc_estadop(){
    $(".menu-link").removeClass("active")
    /*const lista= $(".item-nv-menu");
    var actual='';
    for (var i=0;i<lista.length;i++){
        if ($(lista[i]).attr('href')==getPathURL()){
            actual=lista[i];
            break
        }
    }
    lista.removeClass("current-page")
    $(actual).addClass("current-page")*/
}