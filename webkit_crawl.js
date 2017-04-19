"use strict";

var sys = require("system");
var page = require("webpage").create();

console.error = function () {
    sys.stderr.write(Array.prototype.join.call(arguments, ' ') + '\n');
};
console.log = function() {
    sys.stderr.write(Array.prototype.join.call(arguments, ' ') + '\n');
};

phantom.onError = function(msg, trace) {
  var msgStack = ['PHANTOM ERROR: ' + msg];
  if (trace && trace.length) {
    msgStack.push('TRACE:');
    trace.forEach(function(t) {
      msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function +')' : ''));
    });
  }
  console.error(msgStack.join('\n'));
  phantom.exit(1);
};


if (sys.args.length < 3) {
    console.error("Usage: " + sys.args[0] + " <url> <keyword> [timeout = 120]");
    phantom.exit(-1);
}

var url = sys.args[1];
var keyword = sys.args[2];
var timeout = sys.args.length >= 4 ? sys.args[3] : 120;


var url_no_schema = url;
try {
    url_no_schema = url.match(/https?\:\/\/(.+)/)[1];
}
catch (e) {
    console.error("无法从 URL `" + url + "' 中提取不带协议前缀的网址");
    phantom.exit(-2);
}


setTimeout(function() {
    console.log("等待超时，退出(timeout = " + timeout + "s)");
    phantom.exit(-5);
}, timeout * 1000);


page.onInitialized = function() {
};

page.onLoadStarted = function() {
};

page.onLoadFinished = function() {
    /// 页面载入完成，解析页面并做处理
    /// 只有当前页面的 window.location.href 中包括 url_no_schema，且源码包含关键字的时候，才返回结果 
    
    console.log("页面载入完成: " + Array.prototype.join.call(arguments, ' '));
    
    console.log("开始解析页面");
    var rect = page.evaluate(function() {
        return {
            href: window.location.href,
            innerHTML: document.body.innerHTML
        };
    });
    console.log("页面解析完成");
    
    
    /// 1. 判断 window.location.href 是否包含 url_no_schema
    if (rect.href.indexOf(url_no_schema) < 0) {    
        console.log("当前页面的 URL `" + rect.href + "' 不包含我们需要的网址, 跳过");
        return;
    }
    
    /// 2. 判断 HTML 代码中有没有关键字
    if (rect.innerHTML.indexOf(keyword) < 0) {
        console.log("当前页面的源码中没有关键字 `" + keyword + "'，跳过");
        return;
    }
    
    console.log("在页面中发现了关键字 `" + keyword + "'，返回当前页面的源码并退出");
    sys.stdout.write(rect.innerHTML);
    
    page.close();
    phantom.exit(0);
};

page.onUrlChanged = function() {
    console.log("URL 改变: " + Array.prototype.join.call(arguments, ' '));
};

page.onNavigationRequested = function() {
};

page.onRepaintRequested = function() {
};


page.onClosing = function() {
    phantom.exit(0)
};

page.onConsoleMessage = function() {
};

page.onAlert = function() {
};

page.onConfirm = function() {
};

page.onPrompt = function() {
};


page.open(url, function() {    
});
