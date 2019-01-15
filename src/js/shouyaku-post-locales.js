/**
 * List of translated.
 */

/*global ShouyakuPostSelector: */
/*global jQuery: true*/
/*global React: false*/
/*global ReactDOM: false*/

const { __ } = wp.i18n;

class LocaleList extends React.Component {

  constructor( props ) {
    super( props );
    this.state = {
      locale: '',
      posts: [],
      loading: false,
      adding: false,
      availableLocales: {},
      newLocale: ''
    };
  }

  componentDidMount() {
    this.updateList();
    document.getElementById( 'shouyaku-language-selector' ).addEventListener( 'change', this.updateList.bind( this ) );
  }

  updateList() {
    const select = document.getElementById( 'shouyaku-language-selector' );
    this.setState({
      locale: select.value
    }, this.localeChangeHandler );
  }

  localeChangeHandler() {
    const self    = this;
    this.setState({
      loading: true
    }, function() {
      jQuery.get( ShouyakuPostSelector.endpoint, {
        _wpnonce: ShouyakuPostSelector.nonce,
        status: 'any'
      }).done( ( posts ) => {
        posts = posts.filter( ( post ) => {
          if ( post.locale === self.state.locale ) {
            return false;
          } else if ( post.locale === ShouyakuPostSelector.originalLocale && ! self.state.locale ) {
            return false;
          } else {
            return true;
          }
        });
        const availableLocales = {};
        Object.keys( ShouyakuPostSelector.locales ).forEach( ( locale ) => {
          const postLocale = self.state.locale || ShouyakuPostSelector.originalLocale;
          if ( postLocale === locale ) {
            return true; // Skip original post locale.
          }
          // Check if translation exists.
          let notExists = true;
          posts.forEach( ( post ) => {
            if ( post.locale === locale ) {
              notExists = false;
            }
          });
          if ( notExists ) {
            availableLocales[ locale ] = ShouyakuPostSelector.locales[ locale ];
          }
        });
        self.setState({ posts, availableLocales });
      }).fail( self.errorHandler( __( 'Failed to get tranlsations.', 'shouyaku' ) ) ).always( () => {
        self.setState({
          loading: false
        });
      });
    });
  }

  addButtonHandler( e ) {
    e.preventDefault();
    this.setState({ adding: true });
  }

  cancelButtonHandler( e ) {
    e.preventDefault();
    this.setState({ adding: false });
  }

  errorHandler( message ) {
    return ( response, err, xhr ) => {
      if ( response.responseJSON && response.responseJSON.message ) {
        message = response.responseJSON.message;
      }
      alert( message );
    };
  }

  addNewHandler( e ) {
    e.preventDefault();
    this.setState({ loading: true }, () => {
      const self = this;
      jQuery.post( ShouyakuPostSelector.endpoint, {
        _wpnonce: ShouyakuPostSelector.nonce,
        locale: this.state.newLocale,
      } ).done( (response) => {
        self.localeChangeHandler();
      }).fail(this.errorHandler( __( 'Failed to add new translation.', 'shouyaku' ) ) ).always( () => {
        self.setState({
          loading: false,
          newLocale: ''
        });
      } );
    });
  }

  render() {
    const { noTranslation } = ShouyakuPostSelector;
    let divClassName = [ 'shouyaku-translations' ];
    if ( this.state.loading ) {
      divClassName.push( 'shouyaku-translations-loading' );
    }
    return (
      <div className={divClassName.join( ' ' )}>

        { ! this.state.posts.length ? (
          <p className='description'>{ __( 'This post has no translation.', 'shouyaku' ) }</p>
        ) : (
          <ul className='shouyaku-translations-list'>
            { this.state.posts.filter( ( post ) => {
              return post.locale !== this.state.locale;
            }).map( ( post ) => {
              return (
                <li className='shouyaku-translations-item'>
                  <a key={post.id} className='shouyaku-translations-link' href={post.edit_link}>
                    {post.title} ({post.locale})
                  </a>
                </li>
              );
            }) }
          </ul>
        ) }

        { Object.keys( this.state.availableLocales ).length ? (
          ! this.state.adding ? (
            <p className='shouyaku-translations-add'>
              <a href='#' className='shouyaku-translations-add-btn' onClick={ this.addButtonHandler.bind( this ) }
                 title={ __( 'Add translation', 'shouyaku' ) }>
                <span className='dashicons dashicons-plus'/>
              </a>
            </p>
          ) : (
            <p className='shouyaku-translations-form shouyaku-translations-add'>
              <a href='#' className='shouyaku-translations-add-btn' onClick={ this.cancelButtonHandler.bind( this ) }
                 title={ __( 'Cancel', 'shouyaku' ) }>
                <span className='dashicons dashicons-no'/>
              </a>
              <select onChange={ ( e ) => this.setState({ newLocale: e.target.value }) }>
                <option value=''>{ __( 'Select language', 'shouyaku' ) }</option>
                { Object.keys( this.state.availableLocales ).map( ( locale ) => {
                  return <option key={ locale } value={ locale }>{ this.state.availableLocales[ locale ] }</option>;
                }, this ) }
              </select>
              { this.state.newLocale ? (
                <button className='button shouyaku-translations-add-submit' onClick={this.addNewHandler.bind( this )}> { __( 'Add Translation', 'shouyaku' ) } </button>
              ) : (
                <button className='button disabled shouyaku-translations-add-submit' disabled={true}> { __( 'Select Language', 'shouyaku' ) } </button>
              ) }
            </p>
          ) ) : null }
      </div>
    );
  }
}

ReactDOM.render( <LocaleList />, document.getElementById( 'shouyaku-language-list' ) );
