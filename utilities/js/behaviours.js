/**
 * Behaviours v2.1.0
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
// 2.1.0 - 2026-10-07
// Errors in a callback don't stop following callbacks.
// Unknown name in `attach()` doesn't throw.
// Negative and float priorities.
// `attach( name, settings )` and `attach( name, settings, $context )`.
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
      if ( !isPriority( priority ) ) return console.error( `Priority of '${name}' must be a number.` );

      const groups = name.split( ':' );

      let scope = behaviours;
      groups.forEach( ( group, depth ) => {
        if ( (depth === groups.length - 1) && !!scope[group] ) {
          scope = scope[group];

          if ( !!scope[priority] && scope[priority].toString() === callback.toString() )
            return; // prevent duplicated entry
          else {
            const prio = Object.keys( scope ).filter( isPriority )[0] || false;

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
            const priority = Object.keys( scope[group] ).filter( isPriority )[0] || false;

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
     * @param {string|HTMLDocument|HTMLElement} name  [[GROUP:]GROUP:]NAME (optional)
     * @param {HTMLDocument|HTMLElement} $context (optional)
     * @param {object} settings (optional)
     */
    attach: function ( name, $context, settings ) {
      // shortcut: name is settings
      if ( isPlainObject( name ) && !$context && !settings ) {
        settings = name;
        name = '';
      }

      // shortcut: $context is settings (and settings maybe $context)
      if ( isPlainObject( $context ) && !isPlainObject( settings ) ) {
        [$context, settings] = [settings, $context];
      }

      // shortcut: name is $context
      if ( (name instanceof HTMLDocument || name instanceof HTMLElement) && !($context instanceof HTMLDocument || $context instanceof HTMLElement) ) {
        $context = name;
        name = '';
      }

      let callbacks = [];

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

        if ( !scope ) return this;
      }

      // collect all callbacks in scope
      (function addCallbacks( scope ) {
        Object.keys( scope ).forEach( key => {
          if ( isPriority( key ) ) callbacks.push( { priority: key * 1, callback: scope[key] } );
          else addCallbacks( scope[key] );
        } )
      })( scope );

      // sort by priority (stable)
      callbacks.sort( ( a, b ) => a.priority - b.priority );

      callbacks.forEach( ( { callback } ) => {
        // don't let one failing behaviour stop the others
        try {
          callback( $context || document, settings );
        }
        catch ( error ) {
          window.reportError ? reportError( error ) : setTimeout( () => { throw error } );
        }
      } );

      return this;
    }
  };

  function checkForValidName( name ) {
    if ( name.split( ':' ).some( isPriority ) )
      return console.error( "Name (or group) mustn't be numeric." );

    return true;
  }

  // priorities and groups share the same keys, so numeric keys are priorities
  function isPriority( key ) {
    return /^-?\d+(\.\d+)?$/.test( key );
  }

  function isPlainObject( value ) {
    return !!value && value.toString() === '[object Object]';
  }

  // eventually call all behaviours once dom is loaded
  document.addEventListener( 'DOMContentLoaded', function () {
    $[className].attach();
  }, false );

})(window.behavioursScope||window, window.behavioursName||'Behaviours' );
