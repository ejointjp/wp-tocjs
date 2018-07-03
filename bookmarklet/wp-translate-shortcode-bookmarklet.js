var d = document;
var c = d.selection ? d.selection.createRange().text : d.getSelection();
var t = 'Wp Translate Shortcode';
var s = '[wptr label=\'\' uri=\'' + location.href + '\']\n'
      + '[wptr-original]' + c + '[/wptr-original]\n'
      + '[wptr-translated][/wptr-translated]\n'
      + '[/wptr]';

window.prompt(t, s);
void(0);


var d = document;
var c = d.selection ? d.selection.createRange().text : d.getSelection();
var t = 'Wp Translate Shortcode';
var s = '[wptr label=\'\' uri=\'\']\n'
      + '[wptr-original][/wptr-original]\n'
      + '[wptr-translated][/wptr-translated]\n'
      + '[/wptr]';

window.prompt(t, s);
void(0);
