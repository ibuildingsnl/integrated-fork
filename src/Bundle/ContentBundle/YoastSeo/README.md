# YoastSeo (ContentBundle Submodule)

## Purpose
Builds the embedded Yoast SEO frontend assets used by the ContentBundle editor integration.

## Requirements
- Node.js `16.x` (see `package.json` engines)
- Yarn/NPM dependencies installed in this submodule

## Build
From this directory:

```bash
yarn install
yarn build
```

For development watch mode:

```bash
yarn watch
```

Build output targets:
- `../Resources/public/js/YoastSeo.js`
- `../Resources/public/js/webWorker.js`
- `../Resources/public/js/yoastSeoApp.js`

## Verification
- Confirm generated assets exist under `../Resources/public/js`.
- Open content editor SEO integration and verify no runtime JS errors.

## Troubleshooting
- Build failures with Node version mismatch: switch to Node 16 for this submodule.
- Dependency issues in old setups: clear lockfile/node_modules and reinstall with Node 16.

## Legacy Workaround Note
Older setups documented a manual `util.js` patch in `node_modules` for `NODE_DEBUG` handling.
Keep that workaround only if you reproduce the same historical build failure; do not apply by default.
