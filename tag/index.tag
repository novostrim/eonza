<webpage>
    <div style="margin: 20px 0px;text-align:left" if="html">
      <h1 if="html.title">{html.title}</h1>
      <div name="content"></div>
    </div>

    self = this
    if ( opts.page ) {
      enz.DbApi('_webpage', { id: opts.page }, function( data ) {
          if ( data.result != 0 ) {
              self.html = data.result
              self.content.innerHTML = self.html.content
              riot.update()
          }
      })
    }

</webpage>

