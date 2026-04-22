import assert from 'node:assert/strict';
import fs from 'node:fs';

const read = (path) => fs.readFileSync(path, 'utf8');

const routes = read('resources/js/routes/routes.js');

assert.match(
  routes,
  /name:\s*'app\.profile'[\s\S]*?path:\s*'profile'/,
  "Expected app.profile to use relative child path 'profile'"
);

assert.doesNotMatch(
  routes,
  /name:\s*'app\.profile'[\s\S]*?path:\s*'\/profile'/,
  "app.profile must not be absolute '/profile'"
);

const userLayout = read('resources/js/layouts/UserLayout.vue');
const landingNavbar = read('resources/js/layouts/LandingNavbar.vue');
const mainHeader = read('resources/js/layouts/MainHeader.vue');

assert.match(userLayout, /router\.replace\('\/app\/profile'\)/);
assert.match(userLayout, /route:\s*'\/app\/profile'/);
assert.match(landingNavbar, /router\.push\('\/app\/profile'\)/);
assert.match(mainHeader, /'\/app\/profile'/);

console.log('router-profile-route.test: ok');
