$(document).ready(function () {
    $('.sigPad').each(function(i, element) {
        var sig = eval($(element).find('.pad').html());

        minX = Infinity;
        minY = Infinity;
        maxX = 0;
        maxY = 0;

        $(sig).each(function(index, element) {
            if (minX > Math.min(element.lx, element.mx)) 
                minX = Math.min(element.lx, element.mx)
    
            if (minY > Math.min(element.ly, element.my))
                minY = Math.min(element.ly, element.my)
    
            if (maxX < Math.max(element.lx, element.mx))
                maxX = Math.max(element.lx, element.mx)
        
            if (maxY < Math.max(element.ly, element.my))
                maxY = Math.max(element.ly, element.my)
        });

        var cHeight = $('canvas').height();
        var cWidth = $('canvas').width();

        var heightCoef = cHeight/(maxY-minY+2);
        var widthCoef = cWidth/(maxX-minX+2);
        var coefficient = heightCoef > widthCoef ? widthCoef : heightCoef;
        
        $(sig).each(function(index, element) {
        
            element.lx -= minX-1;
            element.mx -= minX-1;
            element.ly -= minY-1;
            element.my -= minY-1;
    
            element.lx *= coefficient;
            element.mx *= coefficient;
            element.ly *= coefficient;
            element.my *= coefficient;
        });

        $(element).signaturePad({displayOnly:true}).regenerate(sig);
    });
});
