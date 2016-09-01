/**
 * Created by Marko on 31.08.2016.
 */




window.addEvent('domready', function() {
    $('ctrl_longitude').addEvent('input', function(){
        var val = this.get('value');
        var arrVal = val.split(',');


        var arrVal = val.split(',');
        if(arrVal.length)
        {
            arrVal.each(function(item,index){
                // Replace all characters after the dot
                item = item.replace(/(.*?)\.(.*)/i, "$1");

                // Replace all non numeric characters
                item = item.replace(/\D/g,'');

                arrVal[index] = item;

            });

        }
        if( $('ctrl_latitude').get('value') < 1 && arrVal.length == 2){
            $('ctrl_longitude').set('value', arrVal[0]);
            $('ctrl_latitude').set('value', arrVal[1]);

        }else{
            val = arrVal.join(', ');
            this.set('value',val);
        }
    });
});