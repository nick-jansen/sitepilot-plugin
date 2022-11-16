/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
    BaseControl,
    Button,
    ExternalLink,
    PanelBody,
    PanelRow,
    Placeholder,
    Spinner,
    ToggleControl,
    TextControl
} = wp.components;

const {
    render,
    Component,
    Fragment
} = wp.element;

class App extends Component {
    constructor() {
        super(...arguments);

        this.state = {
            isAPILoaded: false,
            isAPISaving: false
        };
    }

    componentDidMount() {
        this.setState({ isAPILoaded: true })
    }

    render() {
        if (!this.state.isAPILoaded) {
            return (
                <Placeholder>
                    <Spinner />
                </Placeholder>
            );
        }

        function openChat() {
            window.Beacon('open');
            window.Beacon('navigate', '/ask/');
        }

        function openHelp() {
            window.Beacon('open');
            window.Beacon('navigate', '/answers/');
        }

        return (
            <Fragment>
                <div className="sp-flex sp-flex-wrap sp-items-center sp-justify-center sp-w-full sp-bg-white sp-p-8 sp-text-center sp-mb-8">
                    <div>
                        <h1>{sitepilot.branding_name}</h1>
                    </div>
                    <div>
                        <div
                            title={`Version: ${sitepilot.version}`}
                            className="sp-text-xs sp-ml-2 sp-mt-1 sp-bg-green-100 sp-py-1 sp-px-1 sp-rounded sp-text-green-500 sp-align-middle"
                        >
                            {sitepilot.version}
                        </div>
                    </div>
                </div>

                <PanelBody
                    title={__('Info')}
                    className="sp-max-w-4xl sp-mx-auto sp-bg-white sp-border sp-border-solid sp-border-gray-200 sp-mb-8"
                >
                    <table class="sp-table-fixed sp-divide-y sp-divide-gray-200">
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap">
                                <strong>{__('Server', 'sitepilot')}</strong>
                            </td>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                {sitepilot.server_name}
                            </td>
                        </tr>
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                <strong>{__('PHP Version', 'sitepilot')}</strong>
                            </td>
                            <td class="py-2 whitespace-nowrap">
                                {sitepilot.php_version}
                            </td>
                        </tr>
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                <strong>{__('WordPress Version', 'sitepilot')}</strong>
                            </td>
                            <td class="py-2 whitespace-nowrap">
                                {sitepilot.wp_version}
                            </td>
                        </tr>
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                <strong>{__('Sitepilot Version', 'sitepilot')}</strong>
                            </td>
                            <td class="sp-py-2 sp-whitespace-nowrap">
                                {sitepilot.version}
                            </td>
                        </tr>
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                <strong>{__('Cache Type', 'sitepilot')}</strong>
                            </td>
                            <td class="sp-py-2 sp-whitespace-nowrap">
                                {sitepilot.cache_type}
                            </td>
                        </tr>
                        <tr>
                            <td class="sp-py-2 sp-whitespace-nowrap">
                                <strong>{__('Powered By', 'sitepilot')}</strong>
                            </td>
                            <td class="sp-py-2 sp-whitespace-nowrap sp-pr-4">
                                {sitepilot.powered_by}
                            </td>
                        </tr>
                    </table>
                </PanelBody>

                <PanelBody className={!sitepilot.support_enabled ? 'hidden' : 'sp-max-w-4xl sp-mx-auto sp-bg-white sp-border sp-border-gray-200'}>
                    <h2 class="sp-mt-0">{__('Got a question for us?', 'sitepilot')}</h2>

                    <p>{__('We would love to help you out if you need any help.', 'sitepilot')}</p>

                    <Button
                        isPrimary
                        isLarge
                        onClick={openHelp}
                        className="sp-mr-2"
                    >
                        {__('Help Center', 'sitepilot')}
                    </Button>

                    <Button
                        isDefault
                        isLarge
                        onClick={openChat}
                    >
                        {__('Ask a question', 'sitepilot')}
                    </Button>
                </PanelBody>
            </Fragment>
        );
    }
}

render(
    <App />,
    document.getElementById('sitepilot-dashboard')
);
