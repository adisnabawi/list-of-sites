function copyclip(idc){
    var copyTextarea = document.querySelector('#clipiium-' + idc);
      copyTextarea.focus();
      copyTextarea.select();
      console.log(idc);
    
      try {
        var successful = document.execCommand('copy');
        console.log(successful);
        var msg = successful ? 'successful' : 'unsuccessful';
      } catch (err) {
        console.log('Oops, unable to copy');
      }
}
