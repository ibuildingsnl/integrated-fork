## REQUIREMENTS ##
* NODE v16 needs to be active while compiling and when you do yarn install

* Following must be removed from util.js file in node modules to generate a working application
if (process.env.NODE_DEBUG) {
var debugEnv = process.env.NODE_DEBUG;
debugEnv = debugEnv.replace(/[|\\{}()[\]^$+?.]/g, '\\$&')
.replace(/\*/g, '.*')
.replace(/,/g, '$|^')
.toUpperCase();
debugEnvRegex = new RegExp('^' + debugEnv + '$', 'i');
}
