$(document).ready(function() {
    $.ajax({
        url: "/ajax/info.php",
        method: "get",
        dataType: "json",
        data: {
            btih: $(".files table").data("btih")
        },
        
        success: function(d) {
            if (d.code != 0) {
                console.log(d.message);
                
                $(".files-loading").text("无法加载文件列表：" + d.message);
                $(".files-loading").addClass("text-danger");
                
                return;
            }
            
            
            $(".files-loading").remove();
            
            var totalSize = 0;
            for (k in d.data.files) {
                var file = d.data.files[k];
                
                var td1 = $("<td>").text(file.path);
                var td2 = $("<td>").text(size2readable(file.size));
                var tr = $("<tr>").append(td1).append(td2);                
                
                $(".files table tbody").append(tr);
                
                totalSize += parseFloat(file.size);
            }
            
            var tip = $("<div>").addClass("tip").html("共<strong>" + d.data.files.length + "</strong>个文件，总大小<strong>" + size2readable(totalSize) + "</strong>");
            $(".files").append(tip);
        }
    });
});

function size2readable(size) {
    size = parseFloat(size);
    var map = {
        "TiB" : 1024 * 1024 * 1024 * 1024, 
        "GiB": 1024 * 1024 * 1024,
        "MiB": 1024 * 1024,
        "KiB": 1024
    };
    
    for (k in map) {
        if (size / map[k] > 1) {
            return (size / map[k]).toFixed(2) + " " + k;
        }
    }
    
    return size + " B";
}
