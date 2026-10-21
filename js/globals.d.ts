// Globals from utilities/js and third-party scripts (see eslint.config.mjs).
type BehaviourCallback = ( $context: Document | HTMLElement, settings?: unknown ) => void;

declare const Behaviours: {
	add( name: string, callback: BehaviourCallback, priority?: number ): void;
	remove( name: string, recursive?: boolean ): void;
	attach( name?: string, settings?: unknown, $context?: Document | HTMLElement ): void;
};
declare const Alter: {
	add<T>( hook: string, callback: ( value: T, ...args: never[] ) => T, priority?: number ): typeof Alter;
	remove<T>( hook: string, callback: ( value: T, ...args: never[] ) => T ): typeof Alter;
	do<T>( hook: string, value: T, ...args: unknown[] ): T;
};
declare const BREAKPOINTS: {
	[size: string]: number | ( ( size: string | number ) => boolean );
	up( size: string | number ): boolean;
	down( size: string | number ): boolean;
	mobile: number;
	desktop: number;
};
declare const CCM: { closeWidget(): void };
declare const doSliders: BehaviourCallback | undefined;
