$(function() {


    function nf(a) { //отделение разрядов пробелами
        if(isFinite(a)) {
            var s=a.toString()+"";
            if(s.length>6) {return (s.substring(0,s.length-6)+" "+s.substring(s.length-6,s.length-3)+" "+s.substring(s.length-3));
            } else if(s.length>3) {return (s.substring(0,s.length-3)+" "+s.substring(s.length-3));
            } else return s;
        } else {
            return a;
        }
    }

    function cf(a) {return a.split(" ").join("");} //убрать пробелы

    $('#ut td').each(function() {if(isFinite($(this).html())) {$(this).html(nf($(this).html()));}});

    $("#t_hd table").html($("#ut thead").html());

    function t_hd_size() {
        $("#t_hd td").each(function(i) {
            $("#ut thead td").eq(i).width($("#ut thead td").eq(i).width());
            $(this).width($("#ut thead td").eq(i).width());
        });
        $("#t_hd").width($("#ut").width());
    }

    var ut_top=$("#ut").position().top;

    function t_hd_pos() {
        var ds=$(document).scrollTop();
        if(ds>ut_top) {
            $("#t_hd").fadeIn();
        } else {
            $("#t_hd").hide();
        }
    }

    t_hd_size();
    t_hd_pos();

    $(window).scroll(function() {
        t_hd_pos();
    });

    $(window).resize(function() {
        t_hd_size();
    });

    var i_s=0;
    var s_vozr=1;

    $(".t_hdr td").click(function() {

        if(i_s==$(this).index()) {
            s_vozr=s_vozr*(-1);
        } else {
            s_vozr=1;
            $(".t_hdr .srt").removeClass("srt");
            i_s=$(this).index();
            $(".t_hdr").each(function() { $(this).find("td").eq(i_s).addClass("srt"); });
        }

        $(".t_hdr td").children("s").detach();

        if(s_vozr>0) {
            $(".t_hdr").each(function() { $(this).find("td").eq(i_s).prepend("<s>&#9660;</s>"); });
        } else {
            $(".t_hdr").each(function() { $(this).find("td").eq(i_s).prepend("<s>&#9650;</s>"); });
        }

        var multi=[];
        $("#ut tbody tr").each(function() {
            var z=$(this).children("td").eq(i_s).html().split(" ").join("");
            z=z.split("А").join("");
            z=z.split("Б").join("");
            if(isFinite(z)) {z=parseInt(z);} else {z=z.length;}
            multi.push([z,$(this).attr("id")]);
        });

        function sName(a,b) {
            if(a[0]<b[0]) {
                return (-1)*s_vozr;
            } else if(a[0]>b[0]) {
                return s_vozr;
            } else {
                return 0;
            }
        }

        multi.sort(sName);

        for(var i=0;i<multi.length;i++) {
            $('#'+multi[i][1]).appendTo($("#ut tbody"));
        }
    });

});