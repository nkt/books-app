(function ($) {
  var fireBooks = [];
  var goFireBtn = $('#go-fire');
  $('.fire').click(function (e) {
    e.preventDefault();
    var btn = $(this);
    var book = {
      id: btn.data('id'),
      name: btn.data('name'),
      maxCount: btn.data('count'),
      count: 0
    };
    for (var i = 0, len = fireBooks.length; i < len; i++) {
      if (fireBooks[i].id == book.id) {
        fireBooks[i] = changeBookCount(fireBooks[i], +prompt('Сколько еще "' + book.name + '" вы хотите списать?'));
        return;
      }
    }
    book = changeBookCount(book, +prompt('Сколько "' + book.name + '" вы хотите списать?'));
    fireBooks.push(book);
    goFireBtn.fadeIn(300);
  });

  function changeBookCount(book, count) {
    if (book.maxCount - book.count >= count) {
      book.count += count;
    } else {
      alert('Вы пытаетесь списать больше, чем есть');
    }
    return book;
  }

  goFireBtn.click(function (e) {
    e.preventDefault();
    $.post('/acts', {
      reason: prompt('Причина списания'),
      books: fireBooks
    }).done(function () {
      document.location.href = '/acts';
    });
  });
})(jQuery);
