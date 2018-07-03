/*!
 * TocJS v0.3.3
 *
 * Copyright 2017 e-JOINT.jp
 * https://ejointjp.github.io/tocjs
 * MIT license
 */

(function (factory) {
  if (typeof module === "object" && typeof module.exports === "object") {
    module.exports = factory(require("jquery"), window, document);
  } else {
    factory(jQuery, window, document);
  }
}(function($, window, document, undefined){

  'use srtict';

  //見出しに番号をつける
  var numbering = function(html, num){
    html = num + '. ' + html;
    return html;
  };

  //見出し番号の suffixを作成する
  var numberSuffix = function(num) {
    var suffix = num > 0 ? '-' + num : '';
    return suffix;
  };

  $.fn.tocjs = function(options) {

    options = $.extend({
      excludes: 'toc-exclude', //このクラス名がついている見出しは除外する
      headingNumber: true, //見出しに番号をつける
      headings: 'h2, h3', //対象とする見出しの要素をカンマ区切りで
      includes: '', //ここで指定した要素のみを目次対象とする
      min: 4, //目次を表示する最小数
      output: '.toc', //目次を出力する要素を指定する
      title: 'Contents', //目次のタイトル
      titleElement: 'h2', //目次のタイトルを表示する要素
      tocNumber: true //目次に番号をつける
    }, options);

    return this.each(function() {

      var $target, text, $output, headings, headingsArray, excludes, min,
          //見出し番号を作るための配列
          counter = [0],
          //無視フラグ
          excludeFlag = false,
          listItem = [],
          listItemNum;

      $output = $('<div>');

      headings = options.headings
        .replace(/　/g, '')
        .replace(/\s/g, '');

      headingsArray = headings.split(',');
      headingsArray.sort();

      excludes = options.excludes
        .replace(/\./g, '')
        .replace(/\,/g, ' ')
        .replace(/　/g, '')
        .replace(/\s+/, ' ');

      includes = $.trim(options.includes);

      //目次のタイトルを生成する
      if (options.title) {
        $title = $('<' + options.titleElement + '>', {
            'class': 'toc-heading',
            text: options.title
        });
        $output.prepend($title);
      }
      //空のULを作成する
      $list = $('<ul>', {
        class: 'toc-list'
      });

      //オプション[includes]に要素が指定された場合はその要素のみを対象とする｡
      if (includes) {
        $target = $(this).find(includes);
      } else {
        $target = $(this)
          .find(headings)
          .not('.toc-heading');
      }

      //対象要素
      $target.each(function() {

        var i, j, classname, id, number, numberedHtml,
            //要素名を取得
            elem = $(this).prop('tagName').toLowerCase(),
            //要素のHTMLを取得
            html = $(this).html();

        for (i = 0; i < headingsArray.length; i++) {

          //オプション[headings]のi番目（i層目）のH要素なら
          if (elem === headingsArray[i]) {

            //除外フラグが立っている､かつ現階層がフラグを立てた要素より深いときは処理を終了
            if(excludeFlag !== false && excludeFlag < i){
              return;

            } else {
              //そうでなければフラグを解除
              excludeFlag = false;
            }
            //除外クラスが付いている場合はフラグを立てて階層位置を保存し処理を終了
            if($(this).hasClass(excludes)){
              excludeFlag = i;
              return;
            }

            //i層目のカウンターを1増やす
            counter[i]++;

            //一番下の階層のH要素でなければ
            if( i !== headingsArray.length - 1){
              //次の階層のカウンターを0にリセット
              counter[i + 1] = 0;
            }

            //counterから目次番号を作成する
            for (j = 0; j < counter.length; j++) {
              if (j === 0) {
                number = counter[j];
              } else {
                number = number + numberSuffix(counter[j]);
              }
            }

            id = 'toc-item-' + number;
            classname = 'toc-item toc-item-' + (i + 1);

            //H要素にID名をつける
            $(this).attr('id', id);

            //オプション numberがtrueなら､H要素に見出し番号をつける
            if (options.headingNumber) {
              // html = number + '. ' + html;
              numberedHtml = numbering(html, number);
              $(this).html(numberedHtml);
            }
            if(options.tocNumber){
              html = numbering(html, number);
            }
            //リストアイテムに追加していく
            listItemNum = listItem.push('<li class="' + classname + '"><a href="#' + id + '">' + html + '</a></li>');
          }
        }
      });

      if(listItemNum >= options.min){
        $list.append(listItem);
        $output.append($list);
        outputHtml = $output.html();
        $(options.output).append(outputHtml);
      }
    });
  };
}));
