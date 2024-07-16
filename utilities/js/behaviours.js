/**
 * Behaviours v2.0.0
 * https://gist.github.com/enoks/77b98aded9b21e082bfc9625eb5e0870
 *
 * ```
 * Behaviours.add( '[group:[group:]]name', ( $context, settings ) => {}, 10 ); // add behaviour
 * Behaviours.attach( '[group:[group:]]name' ); // call callbacks
 * ```
 *
 * Copyright 2023, Stefan Käsche
 * Licensed under MIT
 */

// Changelog:
//
// 2.0.0 - 2023-08-21
// Refactoring.
//
// ?.?.?0 - ????-??-??
// ???
//
// 1.0.0 - ????-??-??
// Init.

;
(function ( $, className ) {

  var behaviours = {};

  $[className] = {
    /**
     * @param {string} name [[GROUP:]GROUP:]NAME
     * @param {function} callback
     * @param {number} priority (optional)
     */
    add: function ( name, callback, priority = 10 ) {
      if ( !checkForValidName( name ) ) return;

      const groups = name.split( ':' );

      let scope = behaviours;
      groups.forEach( ( group, depth ) => {
        if ( (depth === groups.length - 1) && !!scope[group] ) {
          scope = scope[group];

          if ( !!scope[priority] && scope[priority].toString() === callback.toString() )
            return; // prevent duplicated entry
          else {
            const prio = Object.keys( scope ).filter( key => Number.isInteger( key * 1 ) )[0] || false;

            if ( prio === false ) return scope[priority] = callback;
            else if ( scope[prio].toString() === callback.toString() ) {
              // change priority
              delete scope[prio];
              scope[priority] = callback;
              return;
            }
          }

          return console.error( `A behaviour with the name '${name}' already exists.` );
        }

        if ( !scope[group] ) scope = scope[group] = {}
        else scope = scope[group];

        if ( depth === groups.length -1 ) {
          scope[priority] = callback;
        }
      } )

      return this;
    },

    /**
     * @param {string} name  [[GROUP:]GROUP:]NAME
     * @param {boolean} recursive
     */
    remove: function ( name, recursive = false ) {
      if ( !checkForValidName( name ) ) return;

      const groups = name.split( ':' );

      let scope = behaviours;
      groups.forEach( ( group, depth) => {
        if ( !scope ) return;

        if ( !scope[group] ) {
          return scope = console.error( `A behaviour or group with the name '${name}' doesn't exists.` )
        }

        if ( depth === groups.length - 1 ) {
          if ( recursive ) delete scope[group];
          else {
            const priority = Object.keys( scope[group] ).filter( key => Number.isInteger( key * 1 ) )[0] || false;

            if ( priority === false )
              return console.error(
                  `There is no behaviour named '${name}'. If you meant to remove the group '${name}' please use \`${className}.remove( '${name}', true );\`.`
              );
            else delete scope[group][priority];
          }
        }

        scope = scope[group];
      } );

      return this;
    },

    /**
     * @param {string} name  [[GROUP:]GROUP:]NAME (optional)
     * @param {HTMLDocument|HTMLElement} $context (optional)
     * @param {object} settings (optional)
     */
    attach: function ( name, $context, settings ) {
      // shortcut: name is settings
      if ( name && name.toString() === '[object Object]' && !$context && !settings ) {
        settings = name;
        name = '';
      }

      // shortcut: $context is settings
      if ( settings && settings.toString() !== '[object Object]' && $context && $context.toString() === '[object Object]' ) {
        settings = $context;
        $context = null;
      }

      // shortcut: name is $context
      if ( (name instanceof HTMLDocument || name instanceof HTMLElement) && !($context instanceof HTMLDocument || $context instanceof HTMLElement) ) {
        $context = name;
        name = '';
      }

      let callbacks = {}

      let scope = behaviours;
      if ( name ) {
        if ( !checkForValidName( name ) ) return;

        const groups = name.split( ':' );

        groups.forEach( ( group) => {
          if ( !scope ) return;

          if ( !scope[group] ) {
            return scope = console.error( `A behaviour or group with the name '${name}' doesn't exists.` );
          }

          scope = scope[group];
        } );
      }

      // group all callbacks in scope by priority
      (function addCallbacks( scope ) {
        Object.keys( scope ).forEach( key => {
          if ( Number.isInteger( key * 1 ) ) {
            if ( !callbacks[key] ) callbacks[key] = [];
            callbacks[key].push( scope[key] );
          }
          else addCallbacks( scope[key] );
        } )
      })( scope );

      // sort by key aka priority
      callbacks = Object.keys( callbacks ).sort().reduce(function (result, key) {
        result[key] = callbacks[key];
        return result;
      }, {});

      Object.values( callbacks ).flat().forEach( callback => callback( $context || document, settings ) );

      return this;
    }
  };

  function checkForValidName( name ) {
    if ( /(^|:)\d+(:|$)/.test( name ) )
      return console.error( "Name (or group) mustn't be numeric." );

    return true;
  }

  // eventually call all behaviours once dom is loaded
  document.addEventListener( 'DOMContentLoaded', function () {
    $[className].attach();
  }, false );

})(window.behavioursScope||window, window.behavioursName||'Behaviours' );
