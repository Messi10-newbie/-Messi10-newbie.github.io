import javax.swing.*;
import java.awt.*;

public class AdminDashboard extends JFrame {

    private static final long serialVersionUID = 1L;
    private final SeatManager seatManager;

    public AdminDashboard(SeatManager seatManager) {
        this.seatManager = seatManager;
        setTitle("Admin - Cinema Management Console");
        setSize(400, 350);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));

        JPanel buttonPanel = new JPanel(new GridLayout(5, 1, 10, 10));
        buttonPanel.setBorder(BorderFactory.createEmptyBorder(20, 40, 20, 40));

        JButton manageMoviesBtn  = new JButton("Manage Movies");
        JButton viewBookingsBtn  = new JButton("View All Bookings");
        JButton snackOrdersBtn   = new JButton("View Snack Orders");
        JButton reportBtn        = new JButton("Best Selling Report");
        JButton logoutBtn        = new JButton("Exit to Login");

        manageMoviesBtn.addActionListener(e -> new MovieManagerFrame().setVisible(true));

        viewBookingsBtn.addActionListener(e -> new ViewBookingFrame(this.seatManager).setVisible(true));

        snackOrdersBtn.addActionListener(e -> new SnackOrderFrame().setVisible(true));

        reportBtn.addActionListener(e -> {
            String report = SeatManager.getBestSellingMoviesReport();
            JOptionPane.showMessageDialog(this, report, "Best Selling Movies", JOptionPane.INFORMATION_MESSAGE);
        });

        logoutBtn.addActionListener(e -> {
            dispose();
            Main.showLoginWindow();
        });

        buttonPanel.add(manageMoviesBtn);
        buttonPanel.add(viewBookingsBtn);
        buttonPanel.add(snackOrdersBtn);
        buttonPanel.add(reportBtn);
        buttonPanel.add(logoutBtn);

        add(new JLabel("  Welcome, Admin!", SwingConstants.CENTER), BorderLayout.NORTH);
        add(buttonPanel, BorderLayout.CENTER);
        setLocationRelativeTo(null);
    }
}
