
'use strict';

(function($) {
  window.SrsPlayer = function (dom, url) {
    const self = {
      dom: $(dom),
      url,
    };

    self.play = function() {
      if (document.readyState !== "complete") {
        return setTimeout(self.play, 0);
      }
      self.__play();
    }

    self.__play = function () {
      if (self.url.indexOf('.mp4') > 0) {
        self.dom.prop('src', self.url);
        return console.log(`Play by native for ${self.url}`);
      }

      if (self.url.indexOf('.flv') > 0) {
        if (!flvjs.isSupported()) return console.error(`HTTP-FLV is not supported by browser`);

        const player = flvjs.createPlayer({type: 'flv', url: self.url});
        player.attachMediaElement(self.dom.get(0));
        player.load();
        player.play();
        return console.log(`Play by flv.js for ${self.url}`);
      }

      if (self.url.indexOf('.m3u8') > 0) {
        // See https://stackoverflow.com/a/12905122/17679565
        if (document.createElement('video').canPlayType('application/vnd.apple.mpegURL')) {
          self.dom.prop('src', self.url);
          return console.log(`Play by native for ${self.url}`);
        }

        const player = new Hls();
        player.loadSource(self.url);
        player.attachMedia(self.dom.get(0));
        return console.log(`Play by hls.js for ${self.url}`);
      }

      if (self.url.indexOf('webrtc://') === 0) {
        const sdk = new SrsRtcPlayerAsync();
        self.dom.prop('srcObject', sdk.stream);
        sdk.play(self.url);
        return console.log(`Play by srs.sdk.js for ${self.url}`);
      }

      console.error(`URL is not supported：${self.url}`);
    };

    return self;
  };
})(jQuery);

