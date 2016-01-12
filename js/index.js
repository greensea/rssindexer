$(document).ready(function() {
    setPopularityColor();
});

function setPopularityColor() {
    $(".popularity").each(function (k, v) {
        var obj = $(v);
        var text = obj.text();
        
        if (isNaN(parseInt(text))) {
            obj.addClass("text-muted");
        }
        else {
            var p = parseInt(text);
            var color = '#';
            /// 每 1 个热度增加一个红色调
            if (p < 0) {
                /// 什么都不用做
            }
            else if (p < 16) {
                color += '0';
                color += p.toString(16);
            }
            else if (p < 255) {
                color += p.toString(16);
            }
            else {
                color = '#ff';
            }
            color += '0000';
            
            
            obj.css("color", color);
            if (p > 255) {
                obj.css("font-weight", "bold");
            }
        }
    });
}
