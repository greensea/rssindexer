$(document).ready(function() {
    setPopularityColor();
    renderLikeIcon();
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


function renderLikeIcon() {
    
    $(".like-icon .icon").each(function (k, v) {      
        $(v).on("click", function (e) {
            if ($(e.target).attr("rel") == "like") {
                $(e.target).addClass("heartAnimation").attr("rel", "unlike");
            } else {
                $(e.target).removeClass("heartAnimation").attr("rel", "like");
            }
            
            console.log("开始投票");
            
            getUserIdentity(function (user_identity) {
                console.log("准备请求谷歌 reCAPTCHA token(vote)");
                
                grecaptcha.ready(function() {
                    grecaptcha.execute(window.kotomirss_recaptcha_key, {action: 'vote'}).then(function(recaptcha_token) {
                        $.ajax({
                            url: "/ajax/vote.php",
                            type: "post", 
                            data: {
                                resource_id: $(e.target).attr("data-id"),
                                action: $(e.target).attr("rel") == "like" ? "unvote" : "vote",
                                token: recaptcha_token,
                                user_identity: user_identity,
                            },
                            dataType: "json",
                            success: function (j) {
                                console.log(j);
                                if (j.code == 0) {
                                    $("#like-icon-" + j.data.resource.resource_id).find(".count").text(j.data.resource.vote_score);
                                } else {
                                    console.log(j.message);
                                }
                            },
                        });
                    });
                });
                
            });
        });
        
    });
}


function getUserIdentity(cb) {
    var id = null;
    
    id = window.localStorage.getItem("user_identity");
    
    if (id) {
        console.log("在本地找到了 user_identity", id);
        cb(id);
        return;
    }
    
    
    console.log("准备向谷歌请求 recpatcha，用于请求 user_identity");
    
    grecaptcha.ready(function() {
        grecaptcha.execute(window.kotomirss_recaptcha_key, {action: 'user_identity'}).then(function(token) {
            console.log("谷歌返回了 recaptcha token (user_identity)，准备请求 user_identity", token)
            $.ajax({
                url: "/ajax/user_identity.php",
                type: "post",
                data: {
                    token: token,
                },
                success: function (d) {
                    if (d.code == 0) {
                        console.log("得到一个 user_identity", d.data.user_identity);
                        window.localStorage.setItem("user_identity", d.data.user_identity);
                        cb(d.data.user_identity);
                    }
                }
            });
        });
    });
}
