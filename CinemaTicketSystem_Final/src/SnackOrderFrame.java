import javax.swing.*;
import java.awt.*;
import java.io.*;
import java.util.ArrayList;

public class SnackOrderFrame extends JFrame {

    private static final long   serialVersionUID  = 1L;
    private static final String SNACK_ORDER_FILE  = "snack_orders.txt";

    public SnackOrderFrame() {
        setTitle("Admin - Snack Booking Orders");
        setSize(700, 500);
        setLayout(new BorderLayout(5, 5));

        DefaultListModel<String> listModel = new DefaultListModel<>();
        JList<String>            orderList = new JList<>(listModel);
        orderList.setFont(new Font(Font.MONOSPACED, Font.PLAIN, 12));
        orderList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);

        ArrayList<String> rawLines = new ArrayList<>();

        Runnable loadData = () -> {
            listModel.clear();
            rawLines.clear();
            File file = new File(SNACK_ORDER_FILE);
            if (!file.exists()) {
                listModel.addElement("No snack orders found.");
                return;
            }
            try (BufferedReader br = new BufferedReader(new FileReader(file))) {
                String line;
                int orderCount = 0;
                while ((line = br.readLine()) != null) {
                    String[] parts = line.split("\\|");
                    if (parts.length < 5) continue;
                    orderCount++;
                    rawLines.add(line);
                    listModel.addElement(String.format("Order #%d | %s | %s | RM%s",
                        orderCount, parts[0], parts[1], parts[3]));
                }
            } catch (IOException e) {
                System.err.println("Error loading snack orders: " + e.getMessage());
            }
        };

        loadData.run();

        JButton deleteBtn    = new JButton("Delete Selected");
        JButton deleteAllBtn = new JButton("Delete All");
        JButton refreshBtn   = new JButton("Refresh");
        JButton closeBtn     = new JButton("Close");

        deleteBtn.addActionListener(e -> {
            int index = orderList.getSelectedIndex();
            if (index == -1) {
                JOptionPane.showMessageDialog(this, "Please select an order to delete.");
                return;
            }
            int confirm = JOptionPane.showConfirmDialog(this,
                "Delete this order?", "Confirm", JOptionPane.YES_NO_OPTION);
            if (confirm == JOptionPane.YES_OPTION) {
                rawLines.remove(index);
                try (PrintWriter pw = new PrintWriter(new FileWriter(SNACK_ORDER_FILE))) {
                    for (String l : rawLines) pw.println(l);
                } catch (IOException ex) {
                    System.err.println("Error saving: " + ex.getMessage());
                }
                loadData.run();
                JOptionPane.showMessageDialog(this, "Order deleted!");
            }
        });

        deleteAllBtn.addActionListener(e -> {
            if (rawLines.isEmpty()) {
                JOptionPane.showMessageDialog(this, "No orders to delete.");
                return;
            }
            int confirm = JOptionPane.showConfirmDialog(this,
                "Delete ALL snack orders?", "Confirm", JOptionPane.YES_NO_OPTION);
            if (confirm == JOptionPane.YES_OPTION) {
                try { new FileWriter(SNACK_ORDER_FILE).close(); }
                catch (IOException ex) { System.err.println("Error: " + ex.getMessage()); }
                loadData.run();
                JOptionPane.showMessageDialog(this, "All orders deleted!");
            }
        });

        refreshBtn.addActionListener(e -> loadData.run());
        closeBtn.addActionListener(e -> dispose());

        JPanel bottomPanel = new JPanel(new FlowLayout());
        bottomPanel.add(deleteBtn);
        bottomPanel.add(deleteAllBtn);
        bottomPanel.add(refreshBtn);
        bottomPanel.add(closeBtn);

        add(new JLabel("  All Customer Snack Orders:"), BorderLayout.NORTH);
        add(new JScrollPane(orderList), BorderLayout.CENTER);
        add(bottomPanel, BorderLayout.SOUTH);
        setLocationRelativeTo(null);
    }
}
